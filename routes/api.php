<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::apiResource('events', EventController::class)->only(['index', 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
        ->middleware('booking.cancel');

    Route::put('/bookings/{booking}/approve', [BookingController::class, 'approve'])
        ->middleware('booking.organizer.manage');

    Route::middleware('isOrganizer')->group(function () {
        Route::apiResource('events', EventController::class)->only(['store']);
    });

    Route::middleware(['isOrganizer', 'organizer.owns.event'])->group(function () {
        Route::apiResource('events', EventController::class)->only(['update', 'destroy']);
        Route::post('/events/{event}/tickets', [TicketController::class, 'store']);
    });

    Route::middleware(['isOrganizer', 'organizer.owns.ticket'])->group(function () {
        Route::apiResource('tickets', TicketController::class)->only(['update', 'destroy']);
        Route::post('/tickets/{ticket}/bookings/for-user', [BookingController::class, 'storeForCustomer']);
    });

    Route::middleware('isCustomer')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/tickets/{ticket}/bookings', [BookingController::class, 'store'])
            ->middleware('prevent.double.booking');
        Route::post('/bookings/{booking}/payment', [PaymentController::class, 'store'])
            ->middleware('customer.owns.booking');
        Route::get('/payments/{payment}', [PaymentController::class, 'show'])
            ->middleware('payment.access');
    });
});
