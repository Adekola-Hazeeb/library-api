<?php

namespace App\Services;

use App\Models\Book;
use App\Models\Fine;
use App\Models\Loan;
use App\Models\Member;
use Illuminate\Support\Facades\DB;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;

class ReportService
{
    public function getDashboard(): array
    {
        return [
            'total_members'       => Member::count(),
            'active_members'      => Member::where('status', 'active')->count(),
            'suspended_members'   => Member::where('status', 'suspended')->count(),
            'total_books'         => Book::where('is_retired', false)->count(),
            'total_loans'         => Loan::count(),
            'active_loans'        => Loan::where('status', 'active')->count(),
            'overdue_loans'       => Loan::where('status', 'active')
                                        ->where('due_date', '<', now())
                                        ->count(),
            'total_fines'         => Fine::sum('amount'),
            'unpaid_fines'        => Fine::where('is_paid', false)->sum('amount'),
        ];
    }
    public function getOverdueLoans(): LengthAwarePaginator
    {
        return Loan::query()
            ->with(['bookCopy', 'bookCopy.book', 'member'])
            ->where('status', 'active')
            ->where('due_date', '<', now())
            ->orderBy('due_date', 'asc')
            ->paginate(15);
    }

    public function getLowStock(): Collection
    {
        return Book::query()
            ->withCount(['copies as available_copies_count' => function ($q) {
                $q->where('status', 'available');
            }])
            ->where('is_retired', false)
            ->having('available_copies_count', '<', 2)
            ->orderBy('available_copies_count', 'asc')
            ->get();
    }
    public function getMostBorrowed(): Collection
    {
        return Book::query()
            ->withCount('copies as loans_count')
            ->orderBy('loans_count', 'desc')
            ->limit(10)
            ->get();
    }
}