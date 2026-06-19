<?php

namespace App\Http\Requests\Book;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class StoreBookRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }
    public function rules(): array
    {
        return [
            'title' => 'required|string|max:255|unique:books',
            'isbn' => 'required|string|max:20|unique:books',
            'description' => 'nullable|string',
            'published_year' => 'required|integer|min:1000|max:' . date('Y'),
            'author_ids' => 'required|array|min:1',
            'author_ids.*' => 'exists:authors,id',
            'category_ids' => 'required|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'copies' => 'nullable|integer|min:1|max:50'
        ];
    }
}
