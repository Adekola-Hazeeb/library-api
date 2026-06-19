<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class Member extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $fillable = [
        'member_tier_id',
        'first_name',
        'last_name',
        'email',
        'password',
        'phone_number',
        'status',
        'joined_at',
    ];

    protected $hidden = [
        'password',
    ];

    protected $casts = [
        'joined_at' => 'datetime',
        'password' => 'hashed',
    ];


    public function tier(): BelongsTo
    {
        return $this->belongsTo(MemberTier::class, 'member_tier_id');
    }

    public function loans(): HasMany
    {
        return $this->hasMany(Loan::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    public function fines(): HasMany
    {
        return $this->hasMany(Fine::class);
    }

    public function activeLoans(): HasMany
    {
        return $this->hasMany(Loan::class)
            ->whereIn('status', ['active', 'overdue']);
    }

    public function unpaidFinesAmount(): float
    {
        return $this->fines()
            ->where('is_paid', false)
            ->sum('amount');
    }

    public function isSuspended(): bool
    {
        return $this->status === 'suspended';
    }


    public function isActive(): bool
    {
        return $this->status === 'active';
    }
}
