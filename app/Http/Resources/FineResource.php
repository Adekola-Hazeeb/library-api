<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FineResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'amount' => $this->amount,
            'type' => $this->type,
            'is_paid' => $this->is_paid,
            'paid_at' => $this->paid_at?->toDateTimeString(),
            'member' => $this->whenLoaded('member', fn() => [
                'first_name' => $this->member->first_name,
                'last_name' => $this->member->last_name,
                'email' => $this->member->email,
            ]),
            'loan' => $this->whenLoaded('loan', fn() => [
                'id' => $this->loan->id,
                'due_date' => $this->loan->due_date->toDateTimeString(),
                'returned_at' => $this->loan->returned_at?->toDateTimeString(),
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}