<?php

namespace Tests\Feature;

use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function delivery_endpoint_is_under_500ms_with_large_data()
    {
        $data = [];
        for ($i = 0; $i < 10000; $i++) {
            $data[] = [
                'locale' => 'en',
                'key' => "perf.test.key.{$i}",
                'value' => "This is a test value for record {$i}",
                'tags' => json_encode(['web']),
                'created_at' => now(),
                'updated_at' => now(),
            ];

            if (count($data) === 1000) {
                Content::insert($data);
                $data = [];
            }
        }

        $this->getJson('/api/v1/content/en');

        $start = microtime(true);
        $response = $this->getJson('/api/v1/content/en');
        $duration = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(500, $duration);
    }
}
