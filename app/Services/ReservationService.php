<?php

namespace App\Services;

use App\Exceptions\ReservationException;
use App\Models\Member;
use App\Models\Reservation;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class ReservationService
{
    /* Create a reservation for a member — FIFO queue */
    public function createReservation(User $member, array $data): Reservation
    {
        return DB::transaction(function () use ($member, $data) {
            $alreadyBorrowed = $member->loans()
                ->whereIn('status', ['active', 'overdue'])
                ->whereHas('bookCopy', fn($q) => $q->where('book_id', $data['book_id']))
                ->exists();

            if ($alreadyBorrowed) {
                throw new ReservationException(
                    'You already have an active loan for this book.'
                );
            }
            $alreadyReserved = $member->reservations()
                ->where('book_id', $data['book_id'])
                ->whereIn('status', ['pending', 'notified'])
                ->exists();

            if ($alreadyReserved) {
                throw new ReservationException(
                    'You already have an active reservation for this book.'
                );
            }

            $lastPosition = Reservation::where('book_id', $data['book_id'])
                ->whereIn('status', ['pending', 'notified'])
                ->max('queue_position') ?? 0;

            return Reservation::create([
                'member_id' => $member->id,
                'book_id' => $data['book_id'],
                'queue_position' => $lastPosition + 1,
                'status' => 'pending',
                'reserved_at' => now(),
            ]);
        });
    }

    public function getAllReservations(array $filters, Member|User $user): LengthAwarePaginator
    {
        return Reservation::query()
            ->with(['book', 'member'])
            ->when($user instanceof Member, fn($q) => $q->where('member_id', $user->id))
            ->when(isset($filters['status']), fn($q) => $q->where('status', $filters['status']))
            ->paginate(15);
    }

    public function cancelReservation(Reservation $reservation): Reservation
    {
        if (!in_array($reservation->status, ['pending', 'notified'])) {
            throw new ReservationException(
                'Only pending or notified reservations can be cancelled.'
            );
        }

        $reservation->update(['status' => 'cancelled']);

        /* Reorder remaining queue positions for this book */
        Reservation::where('book_id', $reservation->book_id)
            ->whereIn('status', ['pending', 'notified'])
            ->where('queue_position', '>', $reservation->queue_position)
            ->decrement('queue_position');

        return $reservation->fresh(['book', 'member']);
    }

    public function notifyNextInQueue(int $bookId): ?Reservation
    {
        $next = Reservation::where('book_id', $bookId)
            ->where('status', 'pending')
            ->orderBy('queue_position')
            ->first();

        if (!$next) {
            return null;
        }

        /* Set 48hr claim window */
        $next->update([
            'status' => 'notified',
            'notified_at' => now(),
            'claim_expires_at' => now()->addHours(48),
        ]);

        return $next;
    }
}