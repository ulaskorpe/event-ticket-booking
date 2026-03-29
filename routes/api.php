<?php

use App\Http\Controllers\Api\AdminUserController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\EventController;
use App\Http\Controllers\Api\TicketController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::get('/events', [EventController::class, 'index']);
Route::get('/events/{event}', [EventController::class, 'show']);

Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::middleware('isAdmin')->group(function () {
        Route::get('/admin/users', [AdminUserController::class, 'index']);
    });

    Route::middleware('isOrganizer')->group(function () {
        Route::post('/events', [EventController::class, 'store']);
    });

    Route::middleware(['isOrganizer', 'organizer.owns.event'])->group(function () {
        Route::put('/events/{event}', [EventController::class, 'update']);
        Route::delete('/events/{event}', [EventController::class, 'destroy']);
        Route::post('/events/{event}/tickets', [TicketController::class, 'store']);
    });

    Route::middleware(['isOrganizer', 'organizer.owns.ticket'])->group(function () {
        Route::put('/tickets/{ticket}', [TicketController::class, 'update']);
        Route::delete('/tickets/{ticket}', [TicketController::class, 'destroy']);
    });

    Route::middleware('isCustomer')->group(function () {
        Route::get('/bookings', [BookingController::class, 'index']);
        Route::post('/tickets/{ticket}/bookings', [BookingController::class, 'store']);
        Route::put('/bookings/{booking}/cancel', [BookingController::class, 'cancel'])
            ->middleware('customer.owns.booking');
    });
});
