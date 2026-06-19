<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoanResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'             => $this->id,
            'status'         => $this->status,
            'borrowed_at'    => $this->borrowed_at->toDateTimeString(),
            'due_date'       => $this->due_date->toDateTimeString(),
            'returned_at'    => $this->returned_at?->toDateTimeString(),
            'renewals_count' => $this->renewals_count,
            'fines_accrued'  => $this->fines_accrued,
            'is_overdue'     => $this->isOverdue(),
            'book'           => $this->whenLoaded('bookCopy', fn() => [
                'title' => $this->bookCopy->book->title,
                'isbn'  => $this->bookCopy->book->isbn,
            ]),
            'member'         => $this->whenLoaded('member', fn() => [
                'first_name' => $this->member->first_name,
                'last_name'  => $this->member->last_name,
                'email'      => $this->member->email,
            ]),
            'created_at'     => $this->created_at->toDateTimeString(),
        ];
    }
}