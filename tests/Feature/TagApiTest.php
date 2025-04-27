<?php

namespace Tests\Feature;

use App\Models\Tag;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TagApiTest extends TestCase
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

    public function testTagIndex()
    {
        Tag::factory()->count(5)->create();

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/tags');

        $response->assertStatus(200)
                 ->assertJsonCount(5);
    }

    public function testTagStore()
    {
        $tagData = [
            'name' => 'new_tag',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tags', $tagData);

        $response->assertStatus(201)
                 ->assertJson([
                     'name' => 'new_tag',
                 ]);

        $this->assertDatabaseHas('tags', ['name' => 'new_tag']);
    }

    public function testTagShow()
    {
        $tag = Tag::factory()->create(['name' => 'test_tag']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/tags/{$tag->id}");

        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'test_tag',
                 ]);
    }

    public function testTagUpdate()
    {
        $tag = Tag::factory()->create(['name' => 'old_tag']);

        $updateData = [
            'name' => 'updated_tag',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/tags/{$tag->id}", $updateData);

        $response->assertStatus(200)
                 ->assertJson([
                     'name' => 'updated_tag',
                 ]);

        $this->assertDatabaseHas('tags', ['name' => 'updated_tag']);
        $this->assertDatabaseMissing('tags', ['name' => 'old_tag']);
    }

    public function testTagDelete()
    {
        $tag = Tag::factory()->create(['name' => 'delete_tag']);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->deleteJson("/api/tags/{$tag->id}");

        $response->assertStatus(204);
        $this->assertDatabaseMissing('tags', ['id' => $tag->id]);
    }

    public function testUniqueTagNameValidation()
    {
        // Create a tag first
        Tag::factory()->create(['name' => 'existing_tag']);

        // Try to create a tag with the same name
        $tagData = [
            'name' => 'existing_tag',
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/tags', $tagData);

        $response->assertStatus(422)
                 ->assertJsonValidationErrors(['name']);
    }
}
