<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ReservationResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'status' => $this->status,
            'queue_position' => $this->queue_position,
            'reserved_at' => $this->reserved_at->toDateTimeString(),
            'notified_at' => $this->notified_at?->toDateTimeString(),
            'claim_expires_at' => $this->claim_expires_at?->toDateTimeString(),
            'is_claim_expired' => $this->isClaimExpired(),
            'book' => $this->whenLoaded('book', fn() => [
                'id' => $this->book->id,
                'title' => $this->book->title,
            ]),
            'member' => $this->whenLoaded('member', fn() => [
                'first_name' => $this->member->first_name,
                'last_name' => $this->member->last_name,
                'email' => $this->member->email,
            ]),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}