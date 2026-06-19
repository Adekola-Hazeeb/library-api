<?php

namespace App\Http\Requests\Book;

use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Foundation\Http\FormRequest;

class UpdateBookRequest extends FormRequest
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
            'title' => 'sometimes|string|max:255|unique:books',
            'isbn' => 'sometimes|string|max:20|unique:books',
            'description' => 'sometimes|string',
            'published_year' => 'sometimes|integer|min:1000|max:' . date('Y'),
            'author_ids' => 'sometimes|array|min:1',
            'author_ids.*' => 'exists:authors,id',
            'category_ids' => 'sometimes|array|min:1',
            'category_ids.*' => 'exists:categories,id',
            'copies' => 'sometimes|integer|min:1|max:50'
        ];
    }
}
