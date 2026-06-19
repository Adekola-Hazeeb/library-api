<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MemberTier extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'max_books',
        'loan_period_days',
        'fine_rate',
    ];

    /* A tier has many members */
    public function members(): HasMany
    {
        return $this->hasMany(Member::class);
    }
}
