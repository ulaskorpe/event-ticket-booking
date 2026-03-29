<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ProcessPaymentRequest;
use App\Http\Resources\PaymentResource;
use App\Http\Responses\ApiResponse;
use App\Models\Booking;
use App\Models\Payment;
use App\Services\PaymentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function __construct(
        private readonly PaymentService $paymentService
    ) {}

    public function store(ProcessPaymentRequest $request, Booking $booking): JsonResponse
    {
        $booking->loadMissing(['payment', 'ticket']);

        if ($booking->status === BookingStatus::Cancelled) {
            return ApiResponse::failure(
                'Cannot process payment for a cancelled booking.',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        if ($booking->payment !== null) {
            return ApiResponse::failure(
                'A payment already exists for this booking.',
                JsonResponse::HTTP_UNPROCESSABLE_ENTITY
            );
        }

        $forceFailure = $request->has('simulate_failure')
            ? $request->boolean('simulate_failure')
            : null;

        $payment = $this->paymentService->processPayment($booking, $forceFailure);

        $payment->load(['booking.ticket.event']);

        return ApiResponse::created(
            (new PaymentResource($payment))->resolve($request),
            'Payment processed (mock).'
        );
    }

    public function show(Request $request, Payment $payment): JsonResponse
    {
        $payment->load(['booking.ticket.event']);

        return ApiResponse::success(
            (new PaymentResource($payment))->resolve($request),
            'Payment retrieved.'
        );
    }
}
