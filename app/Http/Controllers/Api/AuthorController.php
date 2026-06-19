<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StoreAuthorRequest;
use App\Http\Requests\Author\UpdateAuthorRequest;
use App\Http\Resources\AuthorResource;
use App\Models\Author;
use App\Services\AuthorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AuthorController extends Controller
{

    public function __construct(
        private readonly AuthorService $authorService
    ) {}
    public function index(Request $request): JsonResponse
    {
        $authors = $this->authorService->getAllAuthors(
            $request->only(['name'])
        );

        return response()->json([
            'data' => AuthorResource::collection($authors),
            'meta' => [
                'current_page' => $authors->currentPage(),
                'last_page' => $authors->lastPage(),
                'per_page' => $authors->perPage(),
                'total' => $authors->total(),
            ],
        ]);
    }
    public function store(StoreAuthorRequest $request): JsonResponse
    {
        $author = $this->authorService->createAuthor(
            $request->validated()
        );

        return response()->json([
            'message' => 'Author created successfully.',
            'data' => new AuthorResource($author),
        ], 201);
    }
    public function update(UpdateAuthorRequest $request, Author $author): JsonResponse
    {
        $author = $this->authorService->updateAuthor($author, $request->validated());

        return response()->json([
            'message' => 'Author updated successfully.',
            'data' => new AuthorResource($author),
        ]);
    }
}

