<?php

use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\AuthorController;
use App\Http\Controllers\Api\BookController;
use App\Http\Controllers\Api\LoanController;
use App\Http\Controllers\Api\ReservationController;
use App\Http\Controllers\Api\FineController;
use App\Http\Controllers\Api\ReportController;
use App\Http\Controllers\Api\StaffAuthController;
use App\Http\Controllers\Api\StaffController;
use App\Http\Controllers\Api\MemberAuthController;
use App\Http\Controllers\Api\MemberController;
use App\Http\Controllers\Api\MemberTierController;
use Illuminate\Support\Facades\Route;

Route::post('/members', [MemberController::class, 'store']);
Route::prefix('staff')->group(function () {
    Route::post('/login', [StaffAuthController::class, 'login']);

});

Route::prefix('member')->group(function () {

    Route::post('/login', [MemberAuthController::class, 'login']);
});

Route::prefix('staff')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/logout', [StaffAuthController::class, 'logout']);
        Route::get('/me', [StaffAuthController::class, 'me']);
    });


Route::prefix('member')
    ->middleware('auth:sanctum')
    ->group(function () {
        Route::post('/logout', [MemberAuthController::class, 'logout']);
        Route::get('/me', [MemberAuthController::class, 'me']);
    });

Route::middleware(['auth:sanctum', 'role:librarian,admin'])->group(function () {
    Route::post('/categories', [CategoryController::class, 'store']);
    Route::post('/authors', [AuthorController::class, 'store']);
    Route::patch('/authors/{author}', [AuthorController::class, 'update']);
    Route::post('/books', [BookController::class, 'store']);
    Route::patch('/books/{book}', [BookController::class, 'update']);
    Route::delete('/books/{book}', [BookController::class, 'destroy']);
    Route::patch('/fines/{fine}/pay', [FineController::class, 'pay']);
    Route::patch('/loans/{loan}/return', [LoanController::class, 'returnLoan']);
    Route::get('/members', [MemberController::class, 'index']);
    Route::get('/members/{member}', [MemberController::class, 'show']);
    Route::patch('/members/{member}/suspend', [MemberController::class, 'suspend']);
    Route::patch('/members/{member}/reinstate', [MemberController::class, 'reinstate']);
    Route::get('/staff', [StaffController::class, 'index']);
    Route::post('/staff', [StaffController::class, 'store']);
    Route::patch('/staff/{staff}', [StaffController::class, 'update']);
    Route::delete('/staff/{staff}', [StaffController::class, 'destroy']);
    Route::get('/reports/dashboard', [ReportController::class, 'dashboard']);
    Route::get('/reports/overdue', [ReportController::class, 'overdue']);
    Route::get('/reports/low-stock', [ReportController::class, 'lowStock']);
    Route::get('/reports/most-borrowed', [ReportController::class, 'mostBorrowed']);
    Route::patch('/members/{member}/upgrade-tier', [MemberController::class, 'upgradeTier']);
});
Route::middleware(['auth:member'])->group(function () {
    Route::post('/loans', [LoanController::class, 'store']);
    Route::patch('/loans/{loan}/renew', [LoanController::class, 'renewLoan']);
    Route::post('/reservations', [ReservationController::class, 'store']);
    Route::delete('/reservations/{reservation}', [ReservationController::class, 'destroy']);
    Route::patch('/members/{member}', [MemberController::class, 'update']);
});
Route::middleware(['auth:sanctum,member'])->group(function () {
    Route::get('/categories', [CategoryController::class, 'index']);
    Route::get('/authors', [AuthorController::class, 'index']);
    Route::get('/books', [BookController::class, 'index']);
    Route::get('/books/{book}', [BookController::class, 'show']);
    Route::get('/loans', [LoanController::class, 'index']);
    Route::get('/loans/{loan}', [LoanController::class, 'show']);
    Route::get('/fines', [FineController::class, 'index']);
    Route::get('/reservations', [ReservationController::class, 'index']);
    Route::get('/member-tiers', [MemberTierController::class, 'index']);
});
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
    Route::post('/member-tiers', [MemberTierController::class, 'store']);
    Route::patch('/member-tiers/{memberTier}', [MemberTierController::class, 'update']);
});