<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Book;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use App\Services\BookService;
use App\Http\Resources\BookResource;
use App\Http\Requests\Book\StoreBookRequest;
use App\Http\Requests\Book\UpdateBookRequest;
use App\Exceptions\BookHasActiveLoansException;

class BookController extends Controller
{
    public function __construct(
        private readonly BookService $bookService
    ) {
    }
    public function index(Request $request): JsonResponse
    {
        $isStaff = auth('sanctum')->check();

        $books = $this->bookService->getAllBooks(
            $request->only(['title', 'author', 'category']),
            $isStaff
        );

        return response()->json([
            'data' => BookResource::collection($books),
            'meta' => [
                'current_page' => $books->currentPage(),
                'last_page' => $books->lastPage(),
                'per_page' => $books->perPage(),
                'total' => $books->total(),
            ],
        ]);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreBookRequest $request): JsonResponse
    {
        $book = $this->bookService->createBook(
            $request->validated()
        );

        return response()->json([
            'message' => 'Book created successfully.',
            'data' => new BookResource($book),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Book $book): JsonResponse
    {
        $book = $this->bookService->getBook($book);

        return response()->json([
            'data' => new BookResource($book),
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateBookRequest $request, Book $book): JsonResponse
    {
        $book = $this->bookService->updateBook($book, $request->validated());

        return response()->json([
            'message' => 'Book updated successfully.',
            'data' => new BookResource($book),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Book $book): JsonResponse
    {
        try {
            $this->bookService->retireBook($book);
            return response()->json([
                'message' => 'Book retired successfully.',
            ]);
        } catch (BookHasActiveLoansException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode());
        }
    }
}
