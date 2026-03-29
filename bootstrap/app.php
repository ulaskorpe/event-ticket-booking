<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->redirectGuestsTo(fn () => route('login'));
        $middleware->redirectUsersTo(fn () => route('dashboard'));
        $middleware->alias([
            'web.auth' => \App\Http\Middleware\EnsureWebUserIsAuthenticated::class,
            'isAdmin' => \App\Http\Middleware\EnsureUserIsAdmin::class,
            'isOrganizer' => \App\Http\Middleware\EnsureUserIsOrganizer::class,
            'isCustomer' => \App\Http\Middleware\EnsureUserIsCustomer::class,
            'organizer.owns.event' => \App\Http\Middleware\EnsureOrganizerOwnsEvent::class,
            'organizer.owns.ticket' => \App\Http\Middleware\EnsureOrganizerOwnsTicket::class,
            'customer.owns.booking' => \App\Http\Middleware\EnsureCustomerOwnsBooking::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        //
    })->create();
