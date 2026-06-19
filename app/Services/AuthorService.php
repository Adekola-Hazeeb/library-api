<?php

namespace App\Services;

use App\Models\Author;
use Illuminate\Pagination\LengthAwarePaginator;

class AuthorService
{
    public function createAuthor(array $data): Author
    {
        return Author::create($data);
    }
    public function getAllAuthors(array $filters): LengthAwarePaginator
    {
        return Author::query()
            ->when(isset($filters['name']), function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            })
            ->paginate(15);
    }
    public function updateAuthor(Author $author, array $data): Author
    {
        $author->update($data);

        return $author->fresh();
    }

}
