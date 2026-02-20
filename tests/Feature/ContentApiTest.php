<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\Content;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class ContentApiTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;

    protected function setUp(): void
    {
        parent::setUp();
        $this->admin = User::factory()->create();
    }

    public function it_can_fetch_translations_by_locale_publicly()
    {
        Content::factory()->create(['locale' => 'en', 'key' => 'greet', 'value' => 'Hello']);

        $response = $this->getJson('/api/v1/content/en');

        $response->assertStatus(200)
            ->assertJson(['greet' => 'Hello']);
    }

    public function it_requires_authentication_for_search()
    {
        $response = $this->getJson('/api/v1/content/search?q=test');

        $response->assertStatus(401);
    }

    /** @test */
    public function it_can_search_translations_when_authenticated()
    {
        Content::factory()->create(['key' => 'unique_key', 'value' => 'Special Value']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/search?q=Special');

        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'unique_key']);
    }

    /** @test */
    public function it_clears_cache_on_new_content_store()
    {

        $this->getJson('/api/v1/content/en');

        $initialVersion = Cache::get("content_version_en", 1);

        $data = [
            'locale' => 'en',
            'key' => 'new.key',
            'value' => 'New Value',
            'tags' => ['web']
        ];

        $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/content', $data);

        $newVersion = Cache::get("content_version_en");
        $this->assertEquals($initialVersion + 1, $newVersion);
    }

    /** @test */
    public function it_handles_korean_characters_in_search()
    {
        Content::factory()->create(['key' => 'ko_test', 'value' => '안녕하세요']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/search?q=녕하세');

        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'ko_test']);
    }

    /** @test */
    public function it_returns_error_for_short_search_query()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/search?q=');

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['q']);
    }

    /** @test */
    public function it_denies_unauthenticated_users_from_storing_content()
    {
        $response = $this->postJson('/api/v1/content', [
            'locale' => 'en',
            'key' => 'secret',
            'value' => 'value'
        ]);

        $response->assertStatus(401);
    }

    /** @test */
    public function it_validates_required_fields_when_storing_content()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/content', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['locale', 'key', 'value']);
    }

    /** @test */
    public function it_updates_existing_content_instead_of_creating_duplicate()
    {
        $existing = Content::factory()->create(['locale' => 'en', 'key' => 'app.name', 'value' => 'Old Name']);

        $payload = [
            'locale' => 'en',
            'key' => 'app.name',
            'value' => 'New Name',
            'tags' => ['web']
        ];

        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/content', $payload);

        $response->assertStatus(201);
        $this->assertEquals('New Name', $existing->refresh()->value);

        $this->assertEquals(1, Content::where('key', 'app.name')->count());
    }

    /** @test */
    public function it_can_search_content_by_query_string()
    {
        $searchTerm = 'SECRET_KEY_123';

        Content::factory()->create([
            'key' => 'search.test',
            'value' => "This record contains {$searchTerm}"
        ]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/content/search?q={$searchTerm}");

        $response->assertStatus(200);

        $response->assertJsonCount(1);
    }

    /** @test */
    public function it_can_filter_content_by_tags()
    {
        Content::factory()->create(['key' => 'k1', 'tags' => ['marketing']]);
        Content::factory()->create(['key' => 'k2', 'tags' => ['legal']]);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/search?tag=marketing');

        $response->assertStatus(200)
            ->assertJsonFragment(['key' => 'k1'])
            ->assertJsonMissing(['key' => 'legal']);
    }

    /** @test */
    public function search_works_with_locale_and_tag_combined()
    {
        Content::factory()->create(['locale' => 'en', 'tags' => ['match'], 'key' => 'correct']);
        Content::factory()->create(['locale' => 'fr', 'tags' => ['match'], 'key' => 'wrong-locale']);

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/search?locale=en&tag=match');

        $response->assertStatus(200)->assertJsonCount(1);
    }

    /** @test */
    public function search_returns_200_on_empty_results()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/search?q=STRING_THAT_DOES_NOT_EXIST');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_returns_404_when_showing_non_existent_content()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/detail/999999');

        $response->assertStatus(404);
    }
    /** @test */
    public function it_fails_to_store_content_with_invalid_data()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->postJson('/api/v1/content', [
                'locale' => '',
                'key' => 'test.key',
                'value' => ''
            ]);

        $response->assertStatus(422);
    }

    /** @test */
    public function it_can_export_content_even_if_empty()
    {
        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson('/api/v1/content/non_existent_locale');

        $response->assertStatus(200);
    }

    /** @test */
    public function it_can_successfully_show_content_detail()
    {
        $content = Content::factory()->create();

        $response = $this->actingAs($this->admin, 'sanctum')
            ->getJson("/api/v1/content/detail/{$content->id}");

        $response->assertStatus(200)
            ->assertJsonFragment([
                'id' => $content->id,
                'key' => $content->key
            ]);
    }
}
