<?php

namespace App\Http\Requests\MemberTier;

use Illuminate\Foundation\Http\FormRequest;

class StoreMemberTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name'             => ['required', 'string', 'max:100', 'unique:member_tiers,name'],
            'max_books'        => ['required', 'integer', 'min:1'],
            'loan_period_days' => ['required', 'integer', 'min:1'],
            'fine_rate'        => ['required', 'numeric', 'min:0'],
        ];
    }
}