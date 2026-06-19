<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Reservation\StoreReservationRequest;
use App\Http\Resources\ReservationResource;
use App\Models\Reservation;
use App\Services\ReservationService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReservationController extends Controller
{
    public function __construct(
        private readonly ReservationService $reservationService
    ) {
    }

    /* GET /api/reservations */
    public function index(Request $request): JsonResponse
    {
        $user = auth('sanctum')->user() ?? auth('member')->user();
        $reservations = $this->reservationService->getAllReservations(
            $request->only(['status']),$user);

        return response()->json([
            'data' => ReservationResource::collection($reservations),
            'meta' => [
                'current_page' => $reservations->currentPage(),
                'last_page' => $reservations->lastPage(),
                'per_page' => $reservations->perPage(),
                'total' => $reservations->total(),
            ],
        ]);
    }

    /* POST /api/reservations — member only */
    public function store(StoreReservationRequest $request): JsonResponse
    {
        try {
            $member = auth('member')->user();
            $reservation = $this->reservationService->createReservation(
                $member,$request->validated()
            );

            return response()->json([
                'message' => 'Reservation created successfully.',
                'data' => new ReservationResource($reservation),
            ], 201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 403);
        }
    }
    public function destroy(Reservation $reservation): JsonResponse
    {
        try {
            $this->reservationService->cancelReservation($reservation);

            return response()->json([
                'message' => 'Reservation cancelled successfully.',
            ],201);

        } catch (\Exception $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], $e->getCode() ?: 403);
        }
    }
}