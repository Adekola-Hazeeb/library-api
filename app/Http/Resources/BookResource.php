<?php

namespace App\Http\Resources;

use App\Http\Resources\AuthorResource;
use App\Http\Resources\CategoryResource;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class BookResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'isbn' => $this->isbn,
            'description' => $this->description,
            'published_year' => $this->published_year,
            'is_retired' => $this->is_retired,
            'authors' => AuthorResource::collection(
                $this->whenLoaded('authors')
            ),
            'categories' => CategoryResource::collection(
                $this->whenLoaded('categories')
            ),
            'copies_count' => $this->whenLoaded(
                'copies',
                fn() => $this->copies->count()
            ),
            'available_copies_count' => $this->whenLoaded(
                'copies',
                fn() => $this->copies
                    ->where('status', 'available')
                    ->count()
            ),
            'created_at' => $this->created_at->toDateTimeString(),
        ];
    }
}