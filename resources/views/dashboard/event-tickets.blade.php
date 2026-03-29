@extends('layouts.app')

@section('title', __('user.dashboard.events_tickets_page_title') . ' — ' . config('app.name'))

@section('content')
<div class="flex-grow-1 p-4 p-lg-5">
    <header class="container py-3 mb-4 border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <nav aria-label="breadcrumb" class="mb-2">
                    <ol class="breadcrumb mb-0 small">
                        <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">{{ __('user.dashboard.title') }}</a></li>
                        <li class="breadcrumb-item active" aria-current="page">{{ __('user.dashboard.events_tickets') }}</li>
                    </ol>
                </nav>
                <h1 class="h4 mb-1">{{ __('user.dashboard.events_tickets_heading', ['event' => $event->title]) }}</h1>
                <p class="text-muted small mb-0">
                    {{ $event->date?->timezone(config('app.timezone'))->format('M.d Y H:i') }}
                    · {{ $event->location }}
                </p>
            </div>
            <a href="{{ route('dashboard') }}" class="btn btn-outline-secondary btn-sm">{{ __('user.dashboard.events_tickets_back') }}</a>
        </div>
    </header>
    <main class="container">
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <h2 class="h6 mb-0">{{ __('user.dashboard.events_tickets') }}</h2>
                @if ($canManageTickets)
                    <button type="button" class="btn btn-primary btn-sm" id="btn-ticket-create">{{ __('user.dashboard.tickets_add') }}</button>
                @endif
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>{{ __('user.dashboard.events_tickets_col_type') }}</th>
                                <th>{{ __('user.dashboard.events_tickets_col_price') }}</th>
                                <th>{{ __('user.dashboard.events_tickets_col_quantity') }}</th>
                                <th>{{ __('user.dashboard.events_tickets_col_updated') }}</th>
                                @if ($canManageTickets)
                                    <th class="text-end">{{ __('user.dashboard.tickets_col_actions') }}</th>
                                @endif
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($event->tickets as $ticket)
                                <tr>
                                    <td>{{ $ticket->type }}</td>
                                    <td>{{ number_format((float) $ticket->price, 2) }}</td>
                                    <td>{{ $ticket->quantity }}</td>
                                    <td class="text-muted small">
                                        {{ $ticket->updated_at?->timezone(config('app.timezone'))->format('M.d Y H:i') ?? '—' }}
                                    </td>
                                    @if ($canManageTickets)
                                        <td class="text-end align-middle">
                                            <div class="d-inline-flex flex-wrap gap-1 justify-content-end align-items-center">
                                                <a
                                                    href="{{ route('dashboard.events.tickets.bookings', [$event, $ticket]) }}"
                                                    class="btn btn-sm btn-outline-secondary"
                                                >{{ __('user.dashboard.tickets_bookings') }}</a>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-primary btn-ticket-edit"
                                                    data-id="{{ $ticket->id }}"
                                                    data-type="{{ e($ticket->type) }}"
                                                    data-price="{{ e((string) $ticket->price) }}"
                                                    data-quantity="{{ $ticket->quantity }}"
                                                >{{ __('user.dashboard.events_edit') }}</button>
                                                <button
                                                    type="button"
                                                    class="btn btn-sm btn-outline-danger btn-ticket-delete"
                                                    data-id="{{ $ticket->id }}"
                                                >{{ __('user.dashboard.events_delete') }}</button>
                                            </div>
                                        </td>
                                    @endif
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="{{ $canManageTickets ? 5 : 4 }}" class="text-muted p-4">{{ __('user.dashboard.events_tickets_empty') }}</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

@if ($canManageTickets)
<div class="modal fade" id="modal-ticket-form" tabindex="-1" aria-labelledby="modal-ticket-form-title" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="form-ticket" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-ticket-form-title">{{ __('user.dashboard.tickets_modal_create') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" id="ticket_id" value="">
                    <div class="mb-3">
                        <label class="form-label" for="ticket_type">{{ __('user.dashboard.tickets_field_type') }}</label>
                        <input type="text" class="form-control" id="ticket_type" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="ticket_price">{{ __('user.dashboard.tickets_field_price') }}</label>
                        <input type="number" class="form-control" id="ticket_price" required min="0" step="0.01">
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="ticket_quantity">{{ __('user.dashboard.tickets_field_quantity') }}</label>
                        <input type="number" class="form-control" id="ticket_quantity" required min="1" step="1">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('user.dashboard.events_cancel') }}</button>
                    <button type="submit" class="btn btn-primary">{{ __('user.dashboard.events_save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@if ($canManageTickets)
@push('scripts')
<script>
(function () {
    const API_BASE = @json($apiBaseUrl);
    const API_TOKEN = @json($apiToken);
    const EVENT_ID = @json($event->id);
    const STR = {
        createTitle: @json(__('user.dashboard.tickets_modal_create')),
        editTitle: @json(__('user.dashboard.tickets_modal_edit')),
        deleteConfirm: @json(__('user.dashboard.tickets_delete_confirm')),
        swalYesDelete: @json(__('user.dashboard.swal_yes_delete')),
        swalCancel: @json(__('user.dashboard.swal_cancel')),
        deletedToast: @json(__('user.dashboard.tickets_deleted_toast')),
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

    const modalEl = document.getElementById('modal-ticket-form');
    const formEl = document.getElementById('form-ticket');
    if (!modalEl || !formEl) return;

    const modal = new bootstrap.Modal(modalEl);
    const titleEl = document.getElementById('modal-ticket-form-title');

    document.getElementById('btn-ticket-create').addEventListener('click', function () {
        formEl.reset();
        document.getElementById('ticket_id').value = '';
        titleEl.textContent = STR.createTitle;
        modal.show();
    });

    document.querySelectorAll('.btn-ticket-edit').forEach(function (btn) {
        btn.addEventListener('click', function () {
            document.getElementById('ticket_id').value = btn.getAttribute('data-id') || '';
            document.getElementById('ticket_type').value = btn.getAttribute('data-type') || '';
            document.getElementById('ticket_price').value = btn.getAttribute('data-price') || '';
            document.getElementById('ticket_quantity').value = btn.getAttribute('data-quantity') || '';
            titleEl.textContent = STR.editTitle;
            modal.show();
        });
    });

    document.querySelectorAll('.btn-ticket-delete').forEach(function (btn) {
        btn.addEventListener('click', function () {
            const id = btn.getAttribute('data-id');
            if (!id) return;
            Swal.fire({
                text: STR.deleteConfirm,
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#dc3545',
                cancelButtonColor: '#6c757d',
                confirmButtonText: STR.swalYesDelete,
                cancelButtonText: STR.swalCancel,
            }).then(function (result) {
                if (!result.isConfirmed) return;
                apiFetch('/tickets/' + id, { method: 'DELETE' })
                    .then(function () {
                        Swal.fire({ icon: 'success', title: STR.deletedToast, timer: 1800, showConfirmButton: false })
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

    formEl.addEventListener('submit', function (e) {
        e.preventDefault();
        const ticketId = document.getElementById('ticket_id').value.trim();
        const payload = {
            type: document.getElementById('ticket_type').value.trim(),
            price: parseFloat(document.getElementById('ticket_price').value),
            quantity: parseInt(document.getElementById('ticket_quantity').value, 10),
        };
        const path = ticketId ? '/tickets/' + ticketId : '/events/' + EVENT_ID + '/tickets';
        const method = ticketId ? 'PUT' : 'POST';
        apiFetch(path, { method: method, body: JSON.stringify(payload) })
            .then(function () {
                modal.hide();
                window.location.reload();
            })
            .catch(function (err) {
                var msg = err.message || STR.genericError;
                if (err.status === 403) msg = STR.forbidden;
                if (err.body && err.body.errors) {
                    msg += '\n' + JSON.stringify(err.body.errors);
                }
                alert(msg);
            });
    });
})();
</script>
@endpush
@endif
