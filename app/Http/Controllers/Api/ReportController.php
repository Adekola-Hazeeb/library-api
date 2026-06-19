<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\LoanResource;
use App\Services\ReportService;
use Illuminate\Http\JsonResponse;

class ReportController extends Controller
{
    public function __construct(
        private readonly ReportService $reportService
    ) {}

    /* GET /api/reports/dashboard */
    public function dashboard(): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->getDashboard(),
        ]);
    }

    /* GET /api/reports/overdue */
    public function overdue(): JsonResponse
    {
        $loans = $this->reportService->getOverdueLoans();

        return response()->json([
            'data' => LoanResource::collection($loans),
            'meta' => [
                'current_page' => $loans->currentPage(),
                'last_page'    => $loans->lastPage(),
                'per_page'     => $loans->perPage(),
                'total'        => $loans->total(),
            ],
        ]);
    }

    /* GET /api/reports/low-stock */
    public function lowStock(): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->getLowStock(),
        ]);
    }

    /* GET /api/reports/most-borrowed */
    public function mostBorrowed(): JsonResponse
    {
        return response()->json([
            'data' => $this->reportService->getMostBorrowed(),
        ]);
    }
}