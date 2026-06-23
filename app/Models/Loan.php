<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'book_copy_id',
        'borrowed_at',
        'due_date',
        'returned_at',
        'status',
        'renewals_count',
        'fines_accrued',
    ];

    protected $casts = [
        'borrowed_at' => 'datetime',
        'due_date' => 'datetime',
        'returned_at' => 'datetime',
        'fines_accrued' => 'decimal:2',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function bookCopy(): BelongsTo
    {
        return $this->belongsTo(BookCopy::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function isOverdue(): bool
    {
        return $this->returned_at === null
            && now()->isAfter($this->due_date);
    }
    public function daysOverdue(): int
    {
        if (!$this->isOverdue()) {
            return 0;
        }

        return (int) $this->due_date->diffInDays(now());
    }

    public function calculateFine(): float
    {
        $member = $this->member()->with('memberTier')->first();
        return $this->daysOverdue() * $member->memberTier->fine_rate;
    }
}
