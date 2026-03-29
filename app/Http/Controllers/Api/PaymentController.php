<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
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

    public function store(Request $request, Booking $booking): JsonResponse
    {
        $request->validate([
            'simulate_failure' => ['sometimes', 'boolean'],
        ]);

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

        $payment = $this->paymentService->mockCharge(
            $booking,
            $request->boolean('simulate_failure')
        );

        if ($payment->status === PaymentStatus::Success) {
            $booking->update(['status' => BookingStatus::Confirmed]);
        }

        $payment->load('booking.ticket.event');

        return ApiResponse::created($payment, 'Payment processed (mock).');
    }

    public function show(Payment $payment): JsonResponse
    {
        $payment->load(['booking.ticket.event']);

        return ApiResponse::success($payment, 'Payment retrieved.');
    }
}
