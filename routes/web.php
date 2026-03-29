<?php

use App\Http\Controllers\Auth\WebAuthController;
use App\Http\Controllers\Web\DashboardController;
use App\Http\Controllers\Web\EventTicketsController;
use App\Http\Controllers\Web\TicketBookingsController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function () {
    Route::get('/login', [WebAuthController::class, 'create'])->name('login');
    Route::post('/login', [WebAuthController::class, 'store']);
});

Route::post('/logout', [WebAuthController::class, 'destroy'])
    ->middleware('web.auth')
    ->name('logout');

Route::middleware('web.auth')->group(function () {
    Route::get('/', function () {
        return redirect()->route('dashboard');
    });

    Route::get('/dashboard', DashboardController::class)->name('dashboard');
    Route::get('/dashboard/events/{event}/tickets', EventTicketsController::class)
        ->name('dashboard.events.tickets');
    Route::get('/dashboard/events/{event}/tickets/{ticket}/bookings', TicketBookingsController::class)
        ->name('dashboard.events.tickets.bookings');
});
