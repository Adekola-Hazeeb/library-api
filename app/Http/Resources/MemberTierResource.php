<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class MemberTierResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name, 
            'max_books' => $this->max_books,
            'loan_period_days' => $this->loan_period_days,
            'fine_rate' => $this->fine_rate,
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}