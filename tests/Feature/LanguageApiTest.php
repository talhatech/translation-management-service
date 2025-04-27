<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LanguageApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
    }

    public function testLanguageIndex()
    {
        Language::factory()->count(3)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/languages');

        $response->assertStatus(200)
                 ->assertJsonCount(3);
    }

    public function testLanguageStore()
    {
        $languageData = [
            'code' => 'pt',
            'name' => 'Portuguese',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/languages', $languageData);

        $response->assertStatus(201)
                 ->assertJson([
                     'code' => 'pt',
                     'name' => 'Portuguese',
                 ]);
    }

    // Other test methods...
}
