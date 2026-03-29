<?php

namespace Tests\Unit;

use App\Services\EventCacheService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use PHPUnit\Framework\Attributes\Test;
use Tests\TestCase;

class EventCacheServiceTest extends TestCase
{
    use RefreshDatabase;

    #[Test]
    public function list_version_bump_changes_index_cache_key(): void
    {
        config(['cache.default' => 'array']);

        $service = new EventCacheService;
        $request = Request::create('/api/events', 'GET', ['search' => 'x']);

        $key1 = $service->indexCacheKey($request);
        $service->bumpListVersion();
        $key2 = $service->indexCacheKey($request);

        $this->assertNotSame($key1, $key2);
    }

    #[Test]
    public function forget_show_removes_cached_entry(): void
    {
        config(['cache.default' => 'array']);

        $service = new EventCacheService;
        Cache::put($service->showCacheKey(5), ['id' => 5], 600);

        $service->forgetShow(5);

        $this->assertNull(Cache::get($service->showCacheKey(5)));
    }
}
