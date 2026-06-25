<?php

namespace App\Console\Commands;

use App\Models\Reservation;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('app:expire-reservations')]
#[Description('Expire unclaimed reservations and notify the next member in queue')]
class ExpireReservations extends Command
{
    public function handle(): void
    {
        $expired = Reservation::where('status', 'notified')
            ->where('claim_expires_at', '<', now())
            ->get();

        if ($expired->isEmpty()) {
            $this->info('No expired reservations found.');
            return;
        }

        foreach ($expired as $reservation) {
            $reservation->update(['status' => 'expired']);

            $this->info("Expired reservation ID {$reservation->id} for book ID {$reservation->book_id}");

            $next = Reservation::where('book_id', $reservation->book_id)
                ->where('status', 'pending')
                ->orderBy('queue_position')
                ->first();

            if ($next) {
                $next->update([
                    'status' => 'notified',
                    'notified_at' => now(),
                    'claim_expires_at' => now()->addHours(48),
                ]);

                $this->info("Notified next in queue — reservation ID {$next->id} member ID {$next->member_id}");
            } else {
                $this->info("No pending reservations remain for book ID {$reservation->book_id}");
            }
        }

        $this->info('Done.');
    }
}