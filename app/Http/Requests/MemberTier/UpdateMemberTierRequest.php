<?php

namespace App\Http\Requests\MemberTier;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateMemberTierRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'name' => [
                'sometimes',
                'string',
                'max:100',
                Rule::unique('member_tiers', 'name')->ignore($this->route('member_tier')),
            ],
            'max_books' => ['sometimes', 'integer', 'min:1'],
            'loan_period_days' => ['sometimes', 'integer', 'min:1'],
            'fine_rate' => ['sometimes', 'numeric', 'min:0'],
        ];
    }
}