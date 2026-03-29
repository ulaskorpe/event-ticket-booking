<?php

namespace App\Services;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

/**
 * Event list/detail caching with versioned list keys for safe invalidation without cache tags.
 */
class EventCacheService
{
    private const INDEX_TTL_SECONDS = 600;

    private const LIST_VERSION_KEY = 'events:list:version';

    public function indexCacheKey(Request $request): string
    {
        $version = (int) Cache::get(self::LIST_VERSION_KEY, 1);
        $query = $request->query();
        ksort($query);

        return 'events:index:v'.$version.':'.hash('sha256', http_build_query($query));
    }

    public function showCacheKey(int $eventId): string
    {
        return 'events:show:'.$eventId;
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function rememberIndex(Request $request, Closure $callback): mixed
    {
        return Cache::remember(
            $this->indexCacheKey($request),
            self::INDEX_TTL_SECONDS,
            $callback
        );
    }

    /**
     * @template T
     *
     * @param  Closure(): T  $callback
     * @return T
     */
    public function rememberShow(int $eventId, Closure $callback): mixed
    {
        return Cache::remember(
            $this->showCacheKey($eventId),
            self::INDEX_TTL_SECONDS,
            $callback
        );
    }

    public function forgetShow(int $eventId): void
    {
        Cache::forget($this->showCacheKey($eventId));
    }

    public function bumpListVersion(): void
    {
        $current = (int) Cache::get(self::LIST_VERSION_KEY, 1);
        Cache::forever(self::LIST_VERSION_KEY, $current + 1);
    }

    public function invalidateEvent(int $eventId): void
    {
        $this->forgetShow($eventId);
        $this->bumpListVersion();
    }
}
