<?php

namespace Tests\Feature;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use RefreshDatabase;

    private $user;
    private $token;
    private $language;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->token = $this->user->createToken('test-token')->plainTextToken;
        $this->language = Language::factory()->create(['code' => 'en']);
    }

    public function testTranslationIndex()
    {
        Translation::factory()->count(5)->create([
            'language_id' => $this->language->id
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/translations');

        $response->assertStatus(200);

        // Check if the response structure matches what we expect
        if ($response->json('data')) {
            // If the data is wrapped in a 'data' key, assert count that way
            $response->assertJsonCount(5, 'data');
        } else {
            // If the data is not wrapped, assert count directly
            $response->assertJsonCount(5);
        }
    }

    public function testTranslationStore()
    {
        $tag = Tag::factory()->create(['name' => 'web']);

        $translationData = [
            'key' => 'welcome_message',
            'value' => 'Welcome to our app',
            'language_id' => $this->language->id,
            'tags' => [$tag->name, 'new_tag']
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->postJson('/api/translations', $translationData);

        $response->assertStatus(201);

        // Check if response is wrapped in data key
        if ($response->json('data')) {
            $response->assertJson([
                'data' => [
                    'key' => 'welcome_message',
                    'value' => 'Welcome to our app',
                ]
            ]);
        } else {
            $response->assertJson([
                'key' => 'welcome_message',
                'value' => 'Welcome to our app',
            ]);
        }

        // Check that the tags were created/associated
        $this->assertDatabaseHas('tags', ['name' => 'new_tag']);

        // Get the created translation to check associations
        $translation = Translation::where('key', 'welcome_message')->first();
        $this->assertNotNull($translation);
        $this->assertCount(2, $translation->tags);
    }

    public function testTranslationUpdate()
    {
        $translation = Translation::factory()->create([
            'key' => 'old_key',
            'value' => 'Old value',
            'language_id' => $this->language->id,
        ]);

        $tag = Tag::factory()->create(['name' => 'mobile']);

        $updateData = [
            'key' => 'new_key',
            'value' => 'New value',
            'tags' => [$tag->name]
        ];

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->putJson("/api/translations/{$translation->id}", $updateData);

        $response->assertStatus(200);

        // Check if response is wrapped in data key
        if ($response->json('data')) {
            $response->assertJson([
                'data' => [
                    'key' => 'new_key',
                    'value' => 'New value',
                ]
            ]);
        } else {
            $response->assertJson([
                'key' => 'new_key',
                'value' => 'New value',
            ]);
        }

        // Check that the tags were updated - reload the translation
        $updatedTranslation = Translation::find($translation->id);
        $this->assertCount(1, $updatedTranslation->tags);
        $this->assertEquals($tag->id, $updatedTranslation->tags->first()->id);
    }


    public function testTranslationExport()
    {
        // Create 10 translations
        for ($i = 1; $i <= 10; $i++) {
            Translation::factory()->create([
                'key' => "key_{$i}",
                'value' => "Value {$i}",
                'language_id' => $this->language->id,
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/export?language=en");

        $response->assertStatus(200)
            ->assertJsonCount(10);

        // Check that one of the translations is in the response
        $response->assertJsonFragment(['key_1' => 'Value 1']);
    }

    public function testTranslationExportWithTags()
    {
        $tag = Tag::factory()->create(['name' => 'web']);

        // Create 5 translations with tag
        for ($i = 1; $i <= 5; $i++) {
            $translation = Translation::factory()->create([
                'key' => "key_{$i}",
                'value' => "Value {$i}",
                'language_id' => $this->language->id,
            ]);
            $translation->tags()->attach($tag->id);
        }

        // Create 5 translations without tag
        for ($i = 6; $i <= 10; $i++) {
            Translation::factory()->create([
                'key' => "key_{$i}",
                'value' => "Value {$i}",
                'language_id' => $this->language->id,
            ]);
        }

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson("/api/export?language=en&tags[]=web");

        $response->assertStatus(200)
            ->assertJsonCount(5);

        // Check that a tagged translation is in the response
        $response->assertJsonFragment(['key_1' => 'Value 1']);

        // Check that an untagged translation is not in the response
        $json = $response->json();
        $this->assertArrayNotHasKey('key_6', $json);
    }

    public function testSearchTranslationsByKey()
    {
        // Create specific translations for searching
        Translation::factory()->create([
            'key' => 'search_test_1',
            'value' => 'First search test',
            'language_id' => $this->language->id,
        ]);

        Translation::factory()->create([
            'key' => 'regular_key',
            'value' => 'Regular value',
            'language_id' => $this->language->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/translations?key=search');

        $response->assertStatus(200);

        // Check if response is wrapped in data key
        if ($response->json('data')) {
            $response->assertJsonCount(1, 'data');
            $response->assertJsonPath('data.0.key', 'search_test_1');
        } else {
            $response->assertJsonCount(1);
            $response->assertJsonPath('0.key', 'search_test_1');
        }
    }

    public function testSearchTranslationsByValue()
    {
        // Create specific translations for searching
        Translation::factory()->create([
            'key' => 'key_1',
            'value' => 'Contains searchable content',
            'language_id' => $this->language->id,
        ]);

        Translation::factory()->create([
            'key' => 'key_2',
            'value' => 'Regular value',
            'language_id' => $this->language->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/translations?value=searchable');

        $response->assertStatus(200);

        // Check if response is wrapped in data key
        if ($response->json('data')) {
            $response->assertJsonCount(1, 'data');
            $response->assertJsonPath('data.0.key', 'key_1');
        } else {
            $response->assertJsonCount(1);
            $response->assertJsonPath('0.key', 'key_1');
        }
    }

    public function testSearchTranslationsByLanguageCode()
    {
        $frenchLanguage = Language::factory()->create(['code' => 'fr']);

        // Create English translation
        Translation::factory()->create([
            'key' => 'key_en',
            'value' => 'English value',
            'language_id' => $this->language->id,
        ]);

        // Create French translation
        Translation::factory()->create([
            'key' => 'key_fr',
            'value' => 'French value',
            'language_id' => $frenchLanguage->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/translations?language_code=fr');

        $response->assertStatus(200);

        // Check if response is wrapped in data key
        if ($response->json('data')) {
            $response->assertJsonCount(1, 'data');
            $response->assertJsonPath('data.0.key', 'key_fr');
        } else {
            $response->assertJsonCount(1);
            $response->assertJsonPath('0.key', 'key_fr');
        }
    }

    public function testSearchTranslationsByTag()
    {
        $tag = Tag::factory()->create(['name' => 'mobile']);

        // Create tagged translation
        $taggedTranslation = Translation::factory()->create([
            'key' => 'key_tagged',
            'value' => 'Tagged value',
            'language_id' => $this->language->id,
        ]);
        $taggedTranslation->tags()->attach($tag->id);

        // Create untagged translation
        Translation::factory()->create([
            'key' => 'key_untagged',
            'value' => 'Untagged value',
            'language_id' => $this->language->id,
        ]);

        $response = $this->withHeaders([
            'Authorization' => 'Bearer ' . $this->token,
        ])->getJson('/api/translations?tag=mobile');

        $response->assertStatus(200);

        // Check if response is wrapped in data key
        if ($response->json('data')) {
            $response->assertJsonCount(1, 'data');
            $response->assertJsonPath('data.0.key', 'key_tagged');
        } else {
            $response->assertJsonCount(1);
            $response->assertJsonPath('0.key', 'key_tagged');
        }
    }
}
