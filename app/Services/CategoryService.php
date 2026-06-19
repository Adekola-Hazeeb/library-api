<?php

namespace App\Services;

use App\Models\Category;
use Illuminate\Pagination\LengthAwarePaginator;

class CategoryService
{
    public function getAllCategories(array $filters): LengthAwarePaginator
    {
        return Category::query()
            ->when(isset($filters['name']), function ($query) use ($filters) {
                $query->where('name', 'like', '%' . $filters['name'] . '%');
            })
            ->paginate(15);
    }

    /* Create and return a new category */
    public function createCategory(array $data): Category
    {
        return Category::create($data);
    }
}