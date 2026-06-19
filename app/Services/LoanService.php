<?php
namespace App\Services;
use App\Exceptions\BookNotAvailableException;
use App\Exceptions\DuplicateLoanException;
use App\Exceptions\MaxBooksExceededException;
use App\Exceptions\MaxRenewalsExceededException;
use App\Exceptions\MemberSuspendedException;
use App\Exceptions\UnpaidFinesException;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Member;
use App\Models\User;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
class LoanService
{
    private const MAX_RENEWALS = 2;
    public function createLoan(User $member, array $data): Loan
    {
        return DB::transaction(function () use ($member, $data) {
            if ($member->isSuspended()) {
                throw new MemberSuspendedException();
            }
            if ($member->unpaidFinesAmount() > 0) {
                throw new UnpaidFinesException();
            }
            $activeLoansCount = $member->loans()
                ->whereIn('status', ['active', 'overdue'])
                ->count();
            if ($activeLoansCount >= $member->memberTier->max_books) {
                throw new MaxBooksExceededException();
            }
            $alreadyBorrowed = $member->loans()
                ->whereIn('status', ['active', 'overdue'])
                ->whereHas('bookCopy', function ($q) use ($data) {
                    $q->where('book_id', $data['book_id']);
                })
                ->exists();
            if ($alreadyBorrowed) {
                throw new DuplicateLoanException();
            }
            $copy = \App\Models\BookCopy::where('book_id', $data['book_id'])
                ->where('status', 'available')
                ->where('condition', '!=', 'damaged')
                ->whereDoesntHave('loans', function ($q) {
                    $q->whereHas('book.reservations', function ($q2) {
                        $q2->where('status', 'notified')
                            ->where('claim_expires_at', '>', now());
                    });
                })
                ->first();
            if (!$copy) {
                throw new BookNotAvailableException();
            }
            $dueDate = now()->addDays($member->memberTier->loan_period_days);
            $loan = Loan::create([
                'member_id' => $member->id,
                'book_copy_id' => $copy->id,
                'borrowed_at' => now(),
                'due_date' => $dueDate,
                'status' => 'active',
                'renewals_count' => 0,
                'fines_accrued' => 0,
            ]);
            $copy->update(['status' => 'borrowed']);
            return $loan->load(['bookCopy', 'bookCopy.book', 'member']);
        });
    }
    public function getAllLoans(array $filters, Member|User $user): LengthAwarePaginator
    {
        return Loan::query()
            ->with(['bookCopy', 'bookCopy.book', 'member'])
            ->when($user instanceof Member, function ($query) use ($user) {
                $query->where('member_id', $user->id);
            })
            ->when(isset($filters['status']), function ($query) use ($filters) {
                $query->where('status', $filters['status']);
            })
            ->paginate(15);
    }
    public function getLoan(Loan $loan): Loan
    {
        return $loan->load(['bookCopy', 'bookCopy.book', 'member']);
    }
    public function returnLoan(Loan $loan): Loan
    {
        return DB::transaction(function () use ($loan) {
            $loan->update([
                'returned_at' => now(),
                'status' => 'returned',
            ]);
            $loan->bookCopy->update(['status' => 'available']);
            $fineAmount = $loan->calculateFine();
            if ($fineAmount > 0) {
                $loan->update(['fines_accrued' => $fineAmount]);
                Fine::create([
                    'member_id' => $loan->member_id,
                    'loan_id' => $loan->id,
                    'amount' => $fineAmount,
                    'type' => 'overdue',
                    'is_paid' => false,
                ]);
                if ($loan->member->unpaidFinesAmount() > 5000) {
                    $loan->member->update(['status' => 'suspended']);
                }
            }
            return $loan->fresh(['bookCopy', 'bookCopy.book', 'member']);
        });
    }
    public function renewLoan(Loan $loan): Loan
    {
        if ($loan->status !== 'active') {
            throw new \Exception('Only active loans can be renewed.', 403);
        }
        if ($loan->renewals_count >= self::MAX_RENEWALS) {
            throw new MaxRenewalsExceededException();
        }
        $newDueDate = $loan->due_date->addDays(
            $loan->member->memberTier->loan_period_days
        );
        $loan->update([
            'due_date' => $newDueDate,
            'renewals_count' => $loan->renewals_count + 1,
        ]);
        return $loan->fresh(['bookCopy', 'bookCopy.book', 'member']);
    }
}