<?php

namespace App\Http\Requests\Loan;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StoreLoanRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            /* Book must exist and must not be retired */
            'book_id' => [
                'required',
                'integer',
                Rule::exists('books', 'id')->where('is_retired', false),
            ],
        ];
    }
}