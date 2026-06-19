<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'isbn',
        'description',
        'published_year',
        'is_retired',
    ];

    protected $casts = [
        'is_retired' => 'boolean',
    ];
    public function authors(): BelongsToMany
    {
        return $this->belongsToMany(Author::class, 'book_author');
    }
    public function categories(): BelongsToMany
    {
        return $this->belongsToMany(Category::class, 'book_category');
    }

    public function copies(): HasMany
    {
        return $this->hasMany(BookCopy::class);
    }
    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }
    public function availableCopies(): HasMany
    {
        return $this->hasMany(BookCopy::class)
            ->where('status', 'available');
    }
    public function hasAvailableCopies(): bool
    {
        return $this->availableCopies()->exists();
    }
    public function hasActiveLoans(): bool
    {
        return $this->copies()
            ->whereHas('loans', function ($query) {
                $query->whereIn('status', ['active', 'overdue']);
            })
            ->exists();
    }
}
