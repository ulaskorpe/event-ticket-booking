<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\StoreEventRequest;
use App\Http\Requests\Api\UpdateEventRequest;
use App\Http\Resources\EventResource;
use App\Http\Responses\ApiResponse;
use App\Models\Event;
use App\Services\EventCacheService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function __construct(
        private readonly EventCacheService $eventCache
    ) {}

    public function index(Request $request): JsonResponse
    {
        $paginator = $this->eventCache->rememberIndex($request, function () use ($request) {
            $query = Event::query()->with('creator');

            if ($search = $request->query('search')) {
                $query->where(function ($q) use ($search) {
                    $q->searchByTitle($search)
                        ->orWhere('location', 'like', '%'.$search.'%')
                        ->orWhere('description', 'like', '%'.$search.'%');
                });
            }

            $query->filterByDate(
                $request->query('from'),
                $request->query('to'),
                'date'
            );

            if ($location = $request->query('location')) {
                $query->where('location', 'like', '%'.$location.'%');
            }

            return $query->orderBy('date')->paginate((int) $request->query('per_page', 15));
        });

        return ApiResponse::paginatedResources($request, $paginator, EventResource::class, 'Events retrieved.');
    }

    public function show(Request $request, Event $event): JsonResponse
    {
        $event = $this->eventCache->rememberShow($event->id, function () use ($event) {
            $event->load(['tickets', 'creator']);

            return $event;
        });

        return ApiResponse::success(
            (new EventResource($event))->resolve($request),
            'Event retrieved.'
        );
    }

    public function store(StoreEventRequest $request): JsonResponse
    {
        $validated = $request->validated();
        $validated['created_by'] = $request->user()->id;

        $event = Event::query()->create($validated);
        $event->load(['tickets', 'creator']);

        return ApiResponse::created(
            (new EventResource($event))->resolve($request),
            'Event created.'
        );
    }

    public function update(UpdateEventRequest $request, Event $event): JsonResponse
    {
        $event->update($request->validated());
        $event->load(['tickets', 'creator']);

        return ApiResponse::success(
            (new EventResource($event))->resolve($request),
            'Event updated.'
        );
    }

    public function destroy(Event $event): JsonResponse
    {
        // Tickets, bookings, and payments are removed via DB cascade (event_id → ticket_id → booking_id).
        $event->delete();

        return ApiResponse::success(null, 'Event deleted.');
    }
}
