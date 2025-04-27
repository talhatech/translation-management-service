<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Tests\TestCase;

class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;


    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;

        // Seed database with languages and tags
        Language::factory()->create(['code' => 'en']);
        Language::factory()->create(['code' => 'fr']);
        Tag::factory()->create(['name' => 'web']);
        Tag::factory()->create(['name' => 'mobile']);

        // Create a reasonable number of translations for testing
        Artisan::call('translations:populate', ['count' => 1000]);
    }

    public function testExportEndpointPerformance()
    {
        $start = microtime(true);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/export?language=en');

        $end = microtime(true);
        $executionTime = ($end - $start) * 1000; // Convert to milliseconds

        $response->assertStatus(200);

        // Check if response time is within acceptable limits (500ms)
        $this->assertLessThan(500, $executionTime, "Export endpoint response time is {$executionTime}ms, which exceeds the 500ms limit");
    }

    public function testApiResponseTimes()
    {
        // Test languages index
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/languages');

        $response->assertStatus(200);
        $responseTime = $response->headers->get('X-Response-Time');

        // Extract milliseconds from header
        preg_match('/(\d+)ms/', $responseTime, $matches);
        $timeMs = isset($matches[1]) ? (int)$matches[1] : 1000;

        $this->assertLessThan(200, $timeMs, "Languages index response time is {$timeMs}ms, which exceeds the 200ms limit");

        // Test translations index with filters
        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/translations?language_code=en&per_page=50');

        $response->assertStatus(200);
        $responseTime = $response->headers->get('X-Response-Time');

        // Extract milliseconds from header
        preg_match('/(\d+)ms/', $responseTime, $matches);
        $timeMs = isset($matches[1]) ? (int)$matches[1] : 1000;

        $this->assertLessThan(200, $timeMs, "Translations index response time is {$timeMs}ms, which exceeds the 200ms limit");
    }
}
