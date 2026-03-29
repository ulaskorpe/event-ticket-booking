<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Responses\ApiResponse;
use App\Models\Event;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EventController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Event::query()->with('creator:id,name,email');

        if ($search = $request->query('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', '%'.$search.'%')
                    ->orWhere('location', 'like', '%'.$search.'%')
                    ->orWhere('description', 'like', '%'.$search.'%');
            });
        }

        if ($request->filled('from')) {
            $query->whereDate('date', '>=', $request->query('from'));
        }

        if ($request->filled('to')) {
            $query->whereDate('date', '<=', $request->query('to'));
        }

        if ($location = $request->query('location')) {
            $query->where('location', 'like', '%'.$location.'%');
        }

        $events = $query->orderBy('date')->paginate((int) $request->query('per_page', 15));

        return ApiResponse::paginated($events, 'Events retrieved.');
    }

    public function show(Event $event): JsonResponse
    {
        $event->load(['tickets', 'creator:id,name,email']);

        return ApiResponse::success($event, 'Event retrieved.');
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['required', 'date'],
            'location' => ['required', 'string', 'max:255'],
        ]);

        $validated['created_by'] = $request->user()->id;

        $event = Event::query()->create($validated);
        $event->load('creator:id,name,email');

        return ApiResponse::created($event, 'Event created.');
    }

    public function update(Request $request, Event $event): JsonResponse
    {
        $validated = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
            'date' => ['sometimes', 'date'],
            'location' => ['sometimes', 'string', 'max:255'],
        ]);

        $event->update($validated);
        $event->load(['tickets', 'creator:id,name,email']);

        return ApiResponse::success($event, 'Event updated.');
    }

    public function destroy(Event $event): JsonResponse
    {
        $event->delete();

        return ApiResponse::success(null, 'Event deleted.');
    }
}
