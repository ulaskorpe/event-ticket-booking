@php
    use App\Enums\BookingStatus;
@endphp
@extends('layouts.app')

@section('title', __('user.dashboard.ticket_bookings_page_title') . ' — ' . config('app.name'))

@section('content')
<div class="flex-grow-1 p-4 p-lg-5">
    <header class="container py-3 mb-4 border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('user.dashboard.title') }}</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('dashboard.events.tickets', $event) }}">{{ __('user.dashboard.events_tickets') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('user.dashboard.tickets_bookings') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 mb-1">{{ __('user.dashboard.ticket_bookings_heading', ['type' => $ticket->type, 'event' => $event->title]) }}</h1>
                <p class="text-muted small mb-1">{{ __('user.dashboard.events_tickets_col_price') }}: {{ number_format((float) $ticket->price, 2) }}</p>
                <p class="text-muted small mb-0">
                    {{ __('user.dashboard.ticket_bookings_capacity', ['total' => $ticket->quantity, 'sold' => $sold, 'available' => $available]) }}
                </p>
            </div>
            <div class="d-flex flex-wrap gap-2 align-items-center">
                @if ($available > 0)
                    <button type="button" class="btn btn-primary btn-sm" id="btn-new-booking" data-bs-toggle="modal" data-bs-target="#modal-new-booking">
                        {{ __('user.dashboard.ticket_bookings_new') }}
                    </button>
                @else
                    <span class="badge text-bg-secondary">{{ __('user.dashboard.ticket_bookings_no_capacity') }}</span>
                @endif
                <a href="{{ route('dashboard.events.tickets', $event) }}" class="btn btn-outline-secondary btn-sm">{{ __('user.dashboard.ticket_bookings_back') }}</a>
            </div>
        </div>
    </header>
    <main class="container">
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('user.dashboard.ticket_bookings_col_id') }}</th>
                                <th>{{ __('user.dashboard.ticket_bookings_col_customer') }}</th>
                                <th>{{ __('user.dashboard.ticket_bookings_col_email') }}</th>
                                <th>{{ __('user.dashboard.ticket_bookings_col_qty') }}</th>
                                <th>{{ __('user.dashboard.ticket_bookings_col_status') }}</th>
                                <th>{{ __('user.dashboard.ticket_bookings_col_payment') }}</th>
                                <th>{{ __('user.dashboard.ticket_bookings_col_created') }}</th>
                                <th class="text-end">{{ __('user.dashboard.ticket_bookings_col_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($bookings as $booking)
                                <tr>
                                    <td class="text-muted small">{{ $booking->id }}</td>
                                    <td>{{ $booking->user?->name ?? '—' }}</td>
                                    <td class="small">{{ $booking->user?->email ?? '—' }}</td>
                                    <td>{{ $booking->quantity }}</td>
                                    <td><span class="badge text-bg-secondary">{{ $booking->status->value }}</span></td>
                                    <td class="small">
                                        @if ($booking->payment)
                                            {{ $booking->payment->status->value }}
                                            @if ($booking->payment->amount !== null)
                                                · {{ number_format((float) $booking->payment->amount, 2) }}
                                            @endif
                                        @else
                                            <span class="text-muted">—</span>
                                        @endif
                                    </td>
                                    <td class="text-muted small">
                                        {{ $booking->created_at?->timezone(config('app.timezone'))->format('M.d Y H:i') ?? '—' }}
                                    </td>
                                    <td class="text-end align-middle">
                                        @if ($booking->status === BookingStatus::Cancelled)
                                            <span class="text-muted small">—</span>
                                        @else
                                            <div class="d-inline-flex flex-wrap gap-1 justify-content-end align-items-center">
                                                @if ($booking->status === BookingStatus::Pending)
                                                    <button
                                                        type="button"
                                                        class="btn btn-sm btn-outline-success btn-booking-approve"
                                                        data-booking-id="{{ $booking->id }}"
                                                    >{{ __('user.dashboard.ticket_bookings_approve') }}</button>
                                                @endif
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger btn-booking-cancel"
                                                    data-booking-id="{{ $booking->id }}"
                                                >{{ __('user.dashboard.ticket_bookings_cancel') }}</button>
                                            </div>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="8" class="text-muted p-4">{{ __('user.dashboard.ticket_bookings_empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

@if ($available > 0)
<div class="modal fade" id="modal-new-booking" tabindex="-1" aria-labelledby="modal-new-booking-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-new-booking" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-new-booking-title">{{ __('user.dashboard.ticket_bookings_modal_new_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p class="small text-muted mb-3">{{ __('user.dashboard.ticket_bookings_capacity', ['total' => $ticket->quantity, 'sold' => $sold, 'available' => $available]) }}</p>
                    <div class="mb-3">
                        <label class="form-label" for="booking_user_id">{{ __('user.dashboard.ticket_bookings_select_customer') }}</label>
                        <select class="form-select" id="booking_user_id" name="user_id" required>
                            <option value="">{{ __('user.dashboard.ticket_bookings_select_customer') }}</option>
                            @foreach ($customers as $c)
                                <option value="{{ $c->id }}">{{ $c->name }} — {{ $c->email }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="booking_quantity">{{ __('user.dashboard.ticket_bookings_quantity') }}</label>
                        <input type="number" class="form-control" id="booking_quantity" name="quantity" required min="1" max="{{ $available }}" value="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('user.dashboard.events_cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('user.dashboard.ticket_bookings_create') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script>
(function () {
    const API_BASE = @json($apiBaseUrl);
    const API_TOKEN = @json($apiToken);
    const TICKET_ID = @json($ticket->id);
    const AVAILABLE = @json($available);
    const STR = {
        cancelConfirm: @json(__('user.dashboard.ticket_bookings_cancel_confirm')),
        approveConfirm: @json(__('user.dashboard.ticket_bookings_approve_confirm')),
        swalYes: @json(__('user.dashboard.swal_yes_confirm')),
        swalCancel: @json(__('user.dashboard.swal_cancel')),
        cancelledToast: @json(__('user.dashboard.booking_cancelled_toast')),
        approvedToast: @json(__('user.dashboard.booking_approved_toast')),
        createdToast: @json(__('user.dashboard.booking_created_toast')),
        genericError: @json(__('user.dashboard.tickets_error_generic')),
        forbidden: @json(__('user.dashboard.tickets_error_forbidden')),
    };

    async function apiFetch(path, options) {
        options = options || {};
        const headers = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + API_TOKEN,
            ...(options.headers || {}),
        };
        if (options.body != null) {
            headers['Content-Type'] = 'application/json';
        }
        const res = await fetch(API_BASE + path, { ...options, headers });
        const json = await res.json().catch(function () { return {}; });
        if (!res.ok) {
            const err = new Error(json.message || STR.genericError);
            err.status = res.status;
            err.body = json;
            throw err;
        }
        return json;
    }

    document.querySelectorAll('.btn-booking-approve').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-booking-id');
            if (!id) return;
            Swal.fire({
                text: STR.approveConfirm,
                icon: 'question',
                showCancelButton: true,
                confirmButtonColor: '#198754',
                cancelButtonColor: '#6c757d',
                confirmButtonText: STR.swalYes,
                cancelButtonText: STR.swalCancel,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                apiFetch('/bookings/' + id + '/approve', { method: 'PUT' })
                    .then(function () {
                        Swal.fire({ icon: 'success', title: STR.approvedToast, timer: 1600, showConfirmButton: false })
                            .then(function () { window.location.reload(); });
                    })
                    .catch(function (e) {
                        Swal.fire({
                            icon: 'error',
                            title: e.status === 403 ? STR.forbidden : (e.message || STR.genericError),
                        });
                    });
            });
        });
    });

    document.querySelectorAll('.btn-booking-cancel').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-booking-id');
            if (!id) return;
            Swal.fire({
                text: STR.cancelConfirm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: STR.swalYes,
                cancelButtonText: STR.swalCancel,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                apiFetch('/bookings/' + id + '/cancel', { method: 'PUT' })
                    .then(function () {
                        Swal.fire({ icon: 'success', title: STR.cancelledToast, timer: 1600, showConfirmButton: false })
                            .then(function () { window.location.reload(); });
                    })
                    .catch(function (e) {
                        Swal.fire({
                            icon: 'error',
                            title: e.status === 403 ? STR.forbidden : (e.message || STR.genericError),
                        });
                    });
            });
        });
    });

    const formNew = document.getElementById('form-new-booking');
    if (formNew && AVAILABLE > 0) {
        formNew.addEventListener('submit', function (e) {
            e.preventDefault();
            const userId = document.getElementById('booking_user_id').value;
            const qty = parseInt(document.getElementById('booking_quantity').value, 10);
            if (!userId || !qty || qty < 1 || qty > AVAILABLE) {
                Swal.fire({ icon: 'warning', title: @json(__('user.dashboard.ticket_bookings_invalid_qty')) });
                return;
            }
            apiFetch('/tickets/' + TICKET_ID + '/bookings/for-user', {
                method: 'POST',
                body: JSON.stringify({ user_id: parseInt(userId, 10), quantity: qty }),
            })
                .then(function () {
                    var modalEl = document.getElementById('modal-new-booking');
                    if (modalEl && window.bootstrap) {
                        bootstrap.Modal.getInstance(modalEl)?.hide();
                    }
                    Swal.fire({ icon: 'success', title: STR.createdToast, timer: 1600, showConfirmButton: false })
                        .then(function () { window.location.reload(); });
                })
                .catch(function (err) {
                    var msg = err.message || STR.genericError;
                    if (err.status === 403) msg = STR.forbidden;
                    if (err.body && err.body.errors) {
                        msg += '\n' + JSON.stringify(err.body.errors);
                    }
                    Swal.fire({ icon: 'error', title: msg });
                });
        });
    }
})();
</script>
@endpush
