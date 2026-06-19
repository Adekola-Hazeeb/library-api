<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'email' => $this->email,
            'phone_number' => $this->phone_number,
            'status' => $this->status,
            'joined_at' => $this->joined_at,
            'tier' => $this->whenLoaded('memberTier', fn() => [
                'id' => $this->memberTier->id,
                'name' => $this->memberTier->name,
                'max_books' => $this->memberTier->max_books,
                'loan_period_days' => $this->memberTier->loan_period_days,
            ]),
            'unpaid_fines' => $this->unpaidFinesAmount(),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}