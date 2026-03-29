<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Http\Responses\ApiResponse;
use App\Models\Payment;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Admin may view any payment; customer only payments for their own bookings.
 */
class EnsureUserCanAccessPayment
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        $payment = $request->route('payment');

        if (! $payment instanceof Payment) {
            return ApiResponse::failure('Payment not found.', Response::HTTP_NOT_FOUND);
        }

        $payment->loadMissing('booking');

        if ($user->role === UserRole::Admin) {
            return $next($request);
        }

        if ($user->role === UserRole::Customer && $payment->booking && (int) $payment->booking->user_id === (int) $user->id) {
            return $next($request);
        }

        return ApiResponse::failure('Forbidden. You cannot access this payment.', Response::HTTP_FORBIDDEN);
    }
}
