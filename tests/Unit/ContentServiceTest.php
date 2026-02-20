<?php

namespace Tests\Unit;

use App\Services\ContentService;
use App\Repositories\ContentRepository;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;
use Mockery;

class ContentServiceTest extends TestCase
{
    /** @test */
    public function it_increments_version_when_clearing_cache()
    {
        $repo = Mockery::mock(ContentRepository::class);

        $repo->shouldReceive('upsert')
            ->once()
            ->andReturn((object) ['locale' => 'en']);

        $service = new ContentService($repo);

        Cache::shouldReceive('get')
            ->once()
            ->with('content_version_en', 1)
            ->andReturn(1);

        Cache::shouldReceive('forever')
            ->once()
            ->with('content_version_en', 2);

        $service->storeContent(['locale' => 'en', 'key' => 'test', 'value' => 'val']);
    }
}