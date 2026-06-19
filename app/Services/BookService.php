<?php

namespace App\Services;

use App\Exceptions\BookHasActiveLoansException;
use App\Models\Book;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;

class BookService
{
    public function getAllBooks(array $filters, bool $isStaff): LengthAwarePaginator
    {
        return Book::query()
            ->with(['authors', 'categories', 'copies'])
            ->when(!$isStaff, function ($query) {
                $query->where('is_retired', false);
            })
            ->when(isset($filters['title']), function ($query) use ($filters) {
                $query->where('title', 'like', '%' . $filters['title'] . '%');
            })
            ->when(isset($filters['author']), function ($query) use ($filters) {
                $query->whereHas('authors', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['author'] . '%');
                });
            })
            ->when(isset($filters['category']), function ($query) use ($filters) {
                $query->whereHas('categories', function ($q) use ($filters) {
                    $q->where('name', 'like', '%' . $filters['category'] . '%');
                });
            })
            ->paginate(15);
    }

    public function getBook(Book $book): Book
    {
        return $book->load(['authors', 'categories', 'copies']);
    }
    public function createBook(array $data): Book
    {
        return DB::transaction(function () use ($data) {
            $book = Book::create([
                'title' => $data['title'],
                'isbn' => $data['isbn'],
                'description' => $data['description'] ?? null,
                'published_year' => $data['published_year'],
            ]);
            $book->authors()->attach($data['author_ids']);
            $book->categories()->attach($data['category_ids']);

            if (!empty($data['copies'])) {
                $this->createCopies($book, $data['copies']);
            }
            return $book->fresh(['authors', 'categories', 'copies']);
        });
    }
    public function updateBook(Book $book, array $data): Book
    {
        return DB::transaction(function () use ($book, $data) {
            $book->update(array_filter([
                'title' => $data['title'] ?? null,
                'isbn' => $data['isbn'] ?? null,
                'description' => $data['description'] ?? null,
                'published_year' => $data['published_year'] ?? null,
            ], fn($value) => !is_null($value)));

            if (isset($data['author_ids'])) {
                $book->authors()->sync($data['author_ids']);
            }

            if (isset($data['category_ids'])) {
                $book->categories()->sync($data['category_ids']);
            }

            return $book->fresh(['authors', 'categories', 'copies']);
        });
    }


    public function retireBook(Book $book): Book
    {
        if ($book->hasActiveLoans()) {
            throw new BookHasActiveLoansException();
        }

        $book->update(['is_retired' => true]);

        return $book->fresh();
    }

    private function createCopies(Book $book, int $count): void
    {
        for ($i = 1; $i <= $count; $i++) {
            $book->copies()->create([
                /* Sequential copy number starting from 1 */
                'copy_number' => $i,
                /* New copies are always in good condition */
                'condition' => 'good',
                /* New copies are immediately available for loan */
                'status' => 'available',
            ]);
        }
    }
}
