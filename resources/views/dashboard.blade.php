@extends('layouts.app')

@section('title', __('user.dashboard.page_title') . ' — ' . config('app.name'))

@push('styles')
<link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/dataTables.bootstrap5.min.css">
@endpush

@section('content')
<div class="flex-grow-1 p-4 p-lg-5">
    <header class="container py-3 mb-4 border-bottom">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div>
                <h1 class="h4 mb-1">{{ __('user.dashboard.title') }}</h1>
                <p class="text-muted small mb-0">
                    {{ __('user.dashboard.welcome', ['name' => auth()->user()->name]) }}
                    <span class="opacity-75">{{ __('user.dashboard.role_label', ['role' => auth()->user()->role->value]) }}</span>
                </p>
            </div>
            <form method="post" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="btn btn-outline-secondary btn-sm">{{ __('user.dashboard.logout') }}</button>
            </form>
        </div>
    </header>
    <main class="container">
        @if (! $canManageEvents)
            <p class="text-muted small mb-3">{{ __('user.dashboard.events_read_only_hint') }}</p>
        @endif
        <div class="card shadow-sm">
            <div class="card-header bg-white d-flex flex-wrap align-items-center justify-content-between gap-2 py-3">
                <h2 class="h6 mb-0">{{ __('user.dashboard.events_section_title') }}</h2>
                @if ($canManageEvents)
                    <button type="button" class="btn btn-primary btn-sm" id="btn-event-create">
                        {{ __('user.dashboard.events_add') }}
                    </button>
                @endif
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped table-hover w-100" id="events-table">
                        <thead>
                            <tr>
                             
                                <th>{{ __('user.dashboard.events_col_title') }}</th>
                                <th>{{ __('user.dashboard.events_col_date') }}</th>
                                <th>{{ __('user.dashboard.events_col_location') }}</th>
                                <th>{{ __('user.dashboard.events_col_creator') }}</th>
                                <th class="text-end">{{ __('user.dashboard.events_col_actions') }}</th>
                            </tr>
                        </thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>
</div>

@if ($canManageEvents)
<div class="modal fade" id="modal-event-form" tabindex="-1" aria-labelledby="modal-event-form-title" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <form id="form-event" novalidate>
                <div class="modal-header">
                    <h5 class="modal-title" id="modal-event-form-title">{{ __('user.dashboard.events_modal_create_title') }}</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="event_id" id="event_id" value="">
                    <div class="mb-3">
                        <label class="form-label" for="event_title">{{ __('user.dashboard.events_field_title') }}</label>
                        <input type="text" class="form-control" id="event_title" name="title" required maxlength="255">
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="event_description">{{ __('user.dashboard.events_field_description') }}</label>
                        <textarea class="form-control" id="event_description" name="description" rows="3"></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label" for="event_date">{{ __('user.dashboard.events_field_date') }}</label>
                        <input type="datetime-local" class="form-control" id="event_date" name="date" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label" for="event_location">{{ __('user.dashboard.events_field_location') }}</label>
                        <input type="text" class="form-control" id="event_location" name="location" required maxlength="255">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">{{ __('user.dashboard.events_cancel') }}</button>
                    <button type="submit" class="btn btn-primary" id="btn-event-submit">{{ __('user.dashboard.events_save') }}</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endif
@endsection

@push('scripts')
<script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
<script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.8/js/dataTables.bootstrap5.min.js"></script>
<script>
(function () {
    const API_BASE = @json($apiBaseUrl);
    const API_TOKEN = @json($apiToken);
    const CAN_MANAGE = @json($canManageEvents);
    const DASHBOARD_EVENTS_BASE = @json(rtrim(url('/dashboard/events'), '/'));
    const STR = {
        tickets: @json(__('user.dashboard.events_tickets')),
        edit: @json(__('user.dashboard.events_edit')),
        del: @json(__('user.dashboard.events_delete')),
        createTitle: @json(__('user.dashboard.events_modal_create_title')),
        editTitle: @json(__('user.dashboard.events_modal_edit_title')),
        deleteConfirm: @json(__('user.dashboard.events_delete_confirm')),
        swalYesDelete: @json(__('user.dashboard.swal_yes_delete')),
        swalCancel: @json(__('user.dashboard.swal_cancel')),
        genericError: @json(__('user.dashboard.events_error_generic')),
        forbidden: @json(__('user.dashboard.events_error_forbidden')),
    };

    /** API request: Bearer token; JSON Content-Type only when body is set */
    async function apiFetch(path, options = {}) {
        const headers = {
            'Accept': 'application/json',
            'Authorization': 'Bearer ' + API_TOKEN,
            ...(options.headers || {}),
        };
        if (options.body != null) {
            headers['Content-Type'] = 'application/json';
        }
        const res = await fetch(API_BASE + path, { ...options, headers });
        const json = await res.json().catch(() => ({}));
        if (!res.ok) {
            const err = new Error(json.message || STR.genericError);
            err.status = res.status;
            err.body = json;
            throw err;
        }
        return json;
    }

    /** Converts ISO datetime string to datetime-local input value */
    function isoToDatetimeLocal(iso) {
        if (!iso) return '';
        const d = new Date(iso);
        if (Number.isNaN(d.getTime())) return '';
        const pad = (n) => String(n).padStart(2, '0');
        return d.getFullYear() + '-' + pad(d.getMonth() + 1) + '-' + pad(d.getDate())
            + 'T' + pad(d.getHours()) + ':' + pad(d.getMinutes());
    }

    const columns = [
      
        { data: 'title' },
        {
            data: 'date_formatted',
            defaultContent: '—',
            orderable: false,
        },
        { data: 'location' },
        {
            data: 'creator',
            render: function (data) {
                return data && data.name ? escapeHtml(data.name) : '—';
            },
        },
        {
            data: null,
            orderable: false,
            searchable: false,
            className: 'text-end align-middle',
            render: function (_data, _type, row) {
                const id = row.id;
                const ticketsHref = DASHBOARD_EVENTS_BASE + '/' + id + '/tickets';
                let html = '<div class="d-inline-flex flex-wrap gap-1 justify-content-end align-items-center">';
                html += '<a href="' + ticketsHref + '" class="btn btn-sm btn-outline-secondary">' + escapeHtml(STR.tickets) + '</a>';
                if (CAN_MANAGE) {
                    html += '<button type="button" class="btn btn-sm btn-outline-primary btn-event-edit" data-id="' + id + '">' + escapeHtml(STR.edit) + '</button>';
                    html += '<button type="button" class="btn btn-sm btn-outline-danger btn-event-delete" data-id="' + id + '">' + escapeHtml(STR.del) + '</button>';
                }
                html += '</div>';
                return html;
            },
        },
    ];

    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    const table = $('#events-table').DataTable({
        processing: true,
        serverSide: true,
        searching: true,
        ajax: function (data, callback) {
            const page = Math.floor(data.start / data.length) + 1;
            const params = new URLSearchParams({
                page: String(page),
                per_page: String(data.length),
            });
            if (data.search && data.search.value) {
                params.set('search', data.search.value);
            }
            fetch(API_BASE + '/events?' + params.toString(), {
                headers: {
                    'Accept': 'application/json',
                    'Authorization': 'Bearer ' + API_TOKEN,
                },
            })
                .then(function (r) { return r.json(); })
                .then(function (json) {
                    if (!json.success || !json.data) {
                        callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                        return;
                    }
                    callback({
                        draw: data.draw,
                        recordsTotal: json.data.meta.total,
                        recordsFiltered: json.data.meta.total,
                        data: json.data.items,
                    });
                })
                .catch(function () {
                    callback({ draw: data.draw, recordsTotal: 0, recordsFiltered: 0, data: [] });
                });
        },
        columns: columns,
        order: [[1, 'asc']],
    });

    const modalEl = document.getElementById('modal-event-form');
    const formEl = document.getElementById('form-event');

    if (CAN_MANAGE && modalEl && formEl) {
        const modal = new bootstrap.Modal(modalEl);
        const titleEl = document.getElementById('modal-event-form-title');

        document.getElementById('btn-event-create').addEventListener('click', function () {
            formEl.reset();
            document.getElementById('event_id').value = '';
            titleEl.textContent = STR.createTitle;
            modal.show();
        });

        $('#events-table').on('click', '.btn-event-edit', function () {
            const id = $(this).data('id');
            apiFetch('/events/' + id, { method: 'GET' })
                .then(function (json) {
                    const row = json.data;
                    document.getElementById('event_id').value = String(row.id);
                    document.getElementById('event_title').value = row.title || '';
                    document.getElementById('event_description').value = row.description || '';
                    document.getElementById('event_date').value = isoToDatetimeLocal(row.date);
                    document.getElementById('event_location').value = row.location || '';
                    titleEl.textContent = STR.editTitle;
                    modal.show();
                })
                .catch(function (e) {
                    alert(e.status === 403 ? STR.forbidden : (e.message || STR.genericError));
                });
        });

        $('#events-table').on('click', '.btn-event-delete', function () {
            const id = $(this).data('id');
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
                apiFetch('/events/' + id, { method: 'DELETE' })
                    .then(function () {
                        table.ajax.reload(null, false);
                        Swal.fire({ icon: 'success', title: @json(__('user.dashboard.events_deleted_toast')), timer: 1800, showConfirmButton: false });
                    })
                    .catch(function (e) {
                        Swal.fire({
                            icon: 'error',
                            title: e.status === 403 ? STR.forbidden : (e.message || STR.genericError),
                        });
                    });
            });
        });

        formEl.addEventListener('submit', function (e) {
            e.preventDefault();
            const eventId = document.getElementById('event_id').value;
            const payload = {
                title: document.getElementById('event_title').value.trim(),
                description: document.getElementById('event_description').value.trim() || null,
                date: new Date(document.getElementById('event_date').value).toISOString(),
                location: document.getElementById('event_location').value.trim(),
            };
            const method = eventId ? 'PUT' : 'POST';
            const path = eventId ? '/events/' + eventId : '/events';
            apiFetch(path, { method: method, body: JSON.stringify(payload) })
                .then(function () {
                    modal.hide();
                    table.ajax.reload(null, false);
                })
                .catch(function (err) {
                    let msg = err.message || STR.genericError;
                    if (err.status === 403) msg = STR.forbidden;
                    if (err.body && err.body.errors) {
                        msg += '\n' + JSON.stringify(err.body.errors);
                    }
                    alert(msg);
                });
        });

        modalEl.addEventListener('hidden.bs.modal', function () {
            formEl.classList.remove('was-validated');
        });
    }
})();
</script>
@endpush
