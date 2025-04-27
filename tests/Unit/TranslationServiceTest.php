<?php

namespace Tests\Unit;

use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    private $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TranslationService();
    }

    public function testGetAllLanguages()
    {
        // Create test languages
        Language::factory()->count(3)->create();

        $languages = $this->service->getAllLanguages();

        $this->assertCount(3, $languages);
    }

    public function testCreateTranslation()
    {
        $language = Language::factory()->create(['code' => 'en']);
        $tag1 = Tag::factory()->create(['name' => 'web']);
        $tag2 = Tag::factory()->create(['name' => 'mobile']);

        $data = [
            'key' => 'test_key',
            'value' => 'Test value',
            'language_id' => $language->id,
            'tags' => [$tag1->name, $tag2->name],
        ];

        $translation = $this->service->createTranslation($data);

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertEquals('test_key', $translation->key);
        $this->assertEquals('Test value', $translation->value);
        $this->assertEquals($language->id, $translation->language_id);
        $this->assertCount(2, $translation->tags);
    }

    public function testUpdateTranslation()
    {
        $language = Language::factory()->create(['code' => 'en']);
        $translation = Translation::factory()->create([
            'key' => 'old_key',
            'value' => 'Old value',
            'language_id' => $language->id,
        ]);

        $tag = Tag::factory()->create(['name' => 'web']);

        $data = [
            'key' => 'new_key',
            'value' => 'New value',
            'tags' => [$tag->name],
        ];

        $updated = $this->service->updateTranslation($translation, $data);

        $this->assertEquals('new_key', $updated->key);
        $this->assertEquals('New value', $updated->value);
        $this->assertCount(1, $updated->tags);
        $this->assertEquals($tag->id, $updated->tags->first()->id);
    }

    public function testSearchTranslations()
    {
        $language = Language::factory()->create(['code' => 'en']);

        // Create 10 translations
        Translation::factory()->count(10)->create([
            'language_id' => $language->id,
        ]);

        // Create a specific translation for testing
        $translation = Translation::factory()->create([
            'key' => 'search_test',
            'value' => 'This is a search test',
            'language_id' => $language->id,
        ]);

        $tag = Tag::factory()->create(['name' => 'test_tag']);
        $translation->tags()->attach($tag->id);

        // Search by key
        $results = $this->service->searchTranslations(['key' => 'search']);
        $this->assertGreaterThanOrEqual(1, $results->count());

        // Search by value
        $results = $this->service->searchTranslations(['value' => 'search test']);
        $this->assertGreaterThanOrEqual(1, $results->count());

        // Search by language code
        $results = $this->service->searchTranslations(['language_code' => 'en']);
        $this->assertEquals(11, $results->count()); // 10 + 1

        // Search by tag
        $results = $this->service->searchTranslations(['tag' => 'test_tag']);
        $this->assertEquals(1, $results->count());
    }

    public function testGetTranslationsForLanguage()
    {
        $language = Language::factory()->create(['code' => 'en']);
        $tag = Tag::factory()->create(['name' => 'web']);

        // Create 5 translations with tag
        for ($i = 1; $i <= 5; $i++) {
            $translation = Translation::factory()->create([
                'key' => "key_{$i}",
                'value' => "Value {$i}",
                'language_id' => $language->id,
            ]);
            $translation->tags()->attach($tag->id);
        }

        // Create 5 translations without tag
        for ($i = 6; $i <= 10; $i++) {
            Translation::factory()->create([
                'key' => "key_{$i}",
                'value' => "Value {$i}",
                'language_id' => $language->id,
            ]);
        }

        // Get all translations for language
        $translations = $this->service->getTranslationsForLanguage('en');
        $this->assertCount(10, $translations);

        // Get translations for language with specific tag
        $translations = $this->service->getTranslationsForLanguage('en', ['web']);
        $this->assertCount(5, $translations);
    }
}
