<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Loan\StoreLoanRequest;
use App\Http\Resources\LoanResource;
use App\Models\Loan;
use App\Services\LoanService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LoanController extends Controller
{
    public function __construct(
        private readonly LoanService $loanService
    ) {
    }

    public function index(Request $request): JsonResponse
    {
        /* Determine which guard is authenticated */
        $user = auth('sanctum')->user() ?? auth('member')->user();

        $loans = $this->loanService->getAllLoans(
            $request->only(['status']),
            $user
        );

        return response()->json([
            'data' => LoanResource::collection($loans),
            'meta' => [
                'current_page' => $loans->currentPage(),
                'last_page' => $loans->lastPage(),
                'per_page' => $loans->perPage(),
                'total' => $loans->total(),
            ],
        ]);
    }

    public function show(Loan $loan): JsonResponse
    {
        $member = auth('member')->user();

        if ($member && $loan->member_id !== $member->id) {
            return response()->json(['message' => 'Forbidden.'], 403);
        }

        $loan = $this->loanService->getLoan($loan);

        return response()->json(['data' => new LoanResource($loan)]);
    }

    public function store(StoreLoanRequest $request): JsonResponse
    {
        try {
            $member = auth('member')->user();
            Log::info('Authenticated member ID: ' . $member?->id);
            Log::info('Request book_id: ' . $request->validated()['book_id']);

            $loan = $this->loanService->createLoan(
                $member,
                $request->validated()
            );

            return response()->json([
                'message' => 'Book borrowed successfully.',
                'data' => new LoanResource($loan),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    public function returnLoan(Loan $loan): JsonResponse
    {
        try {
            $loan = $this->loanService->returnLoan($loan);

            return response()->json([
                'message' => 'Book returned successfully.',
                'data' => new LoanResource($loan),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }

    public function renewLoan(Loan $loan): JsonResponse
    {
        try {
            $loan = $this->loanService->renewLoan($loan);

            return response()->json([
                'message' => 'Loan renewed successfully.',
                'data' => new LoanResource($loan),
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 422);
        }
    }
}