<?php

use App\Http\Middleware\EnsureCustomerOwnsBooking;
use App\Http\Middleware\EnsureOrganizerOwnsEvent;
use App\Http\Middleware\EnsureOrganizerOwnsTicket;
use App\Http\Middleware\EnsureUserCanAccessPayment;
use App\Http\Middleware\EnsureOrganizerOrAdminCanManageBooking;
use App\Http\Middleware\EnsureUserCanCancelBooking;
use App\Http\Middleware\EnsureUserIsAdmin;
use App\Http\Middleware\EnsureUserIsCustomer;
use App\Http\Middleware\EnsureUserIsOrganizer;
use App\Http\Middleware\EnsureWebUserIsAuthenticated;
use App\Http\Middleware\PreventDoubleBooking;
use App\Http\Responses\ApiResponse;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\Response;

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
            'web.auth' => EnsureWebUserIsAuthenticated::class,
            'isAdmin' => EnsureUserIsAdmin::class,
            'isOrganizer' => EnsureUserIsOrganizer::class,
            'isCustomer' => EnsureUserIsCustomer::class,
            'organizer.owns.event' => EnsureOrganizerOwnsEvent::class,
            'organizer.owns.ticket' => EnsureOrganizerOwnsTicket::class,
            'customer.owns.booking' => EnsureCustomerOwnsBooking::class,
            'payment.access' => EnsureUserCanAccessPayment::class,
            'prevent.double.booking' => PreventDoubleBooking::class,
            'booking.cancel' => EnsureUserCanCancelBooking::class,
            'booking.organizer.manage' => EnsureOrganizerOrAdminCanManageBooking::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->render(function (AuthenticationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::failure(
                    'Unauthenticated.',
                    Response::HTTP_UNAUTHORIZED
                );
            }
        });

        $exceptions->render(function (ValidationException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::failure(
                    'Validation failed.',
                    Response::HTTP_UNPROCESSABLE_ENTITY,
                    $e->errors()
                );
            }
        });

        $exceptions->render(function (ModelNotFoundException $e, Request $request) {
            if ($request->is('api/*')) {
                return ApiResponse::failure(
                    'Resource not found.',
                    Response::HTTP_NOT_FOUND
                );
            }
        });
    })->create();
