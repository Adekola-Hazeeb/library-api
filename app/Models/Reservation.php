<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'member_id',
        'book_id',
        'queue_position',
        'status',
        'reserved_at',
        'notified_at',
        'claim_expires_at',
    ];
    protected $casts = [
        'reserved_at' => 'datetime',
        'notified_at' => 'datetime',
        'claim_expires_at' => 'datetime',
    ];

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function isClaimExpired(): bool
    {
        return $this->claim_expires_at !== null
            && now()->isAfter($this->claim_expires_at);
    }
}
