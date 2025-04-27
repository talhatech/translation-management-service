<?php

namespace App\Console\Commands;

use App\Models\Tag;
use App\Enums\TagType;
use App\Models\Language;
use Illuminate\Support\Str;
use App\Models\Translation;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;


// todo: command is still handling too many responsibilities (refactor)
class PopulateTranslations extends Command
{
    /**
     * The command signature with default count parameter.
     *
     * @var string
     */
    protected $signature = 'translations:populate {count=10000}';

    /**
     * The command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with sample translations for testing';

    /**
     * The maximum number of records to process in a single chunk.
     *
     * @var int
     */
    private const MAX_CHUNK_SIZE = 1000;

    /**
     * The maximum number of tags to attach to a translation.
     *
     * @var int
     */
    private const MAX_TAGS_PER_TRANSLATION = 3;

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(): int
    {
        $count = (int) $this->argument('count');
        $this->info("Starting to populate {$count} translations...");

        $tags = $this->prepareTags();
        $languages = $this->prepareLanguages();

        $this->generateTranslationsInChunks($count, $languages, $tags);

        $this->info("Successfully populated the database with {$count} translations.");
        return Command::SUCCESS;
    }

    /**
     * Ensure languages exist and return all languages.
     *
     * @return Collection
     */
    private function prepareLanguages(): Collection
    {
        $languagesToCreate = [];

        $languageData = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
        ];

        $existingLanguages = Language::whereIn('code', array_column($languageData, 'code'))
            ->get()
            ->keyBy('code');

        foreach ($languageData as $language) {
            if (!$existingLanguages->has($language['code'])) {
                $languagesToCreate[] = array_merge($language, [
                    'id' => Str::orderedUuid(),
                    'created_at' => now(),
                    'updated_at' => now()
                ]);
            }
        }

        if (!empty($languagesToCreate)) {
            Language::insert($languagesToCreate);
        }

        return Language::all();
    }

    /**
     * Ensure tags exist and return all tags.
     *
     * @return Collection
     */
    private function prepareTags(): Collection
    {
        $tagsToCreate = [];
        $tagValues = array_map(fn($tag) => $tag->value, TagType::cases());

        $existingTags = Tag::whereIn('name', $tagValues)
            ->get()
            ->keyBy('name');


        foreach ($tagValues as $tagValue) {
            if (!$existingTags->has($tagValue)) {
                $tagsToCreate[] = [
                    'id' => Str::orderedUuid(),
                    'name' => $tagValue,
                    'created_at' => now(),
                    'updated_at' => now()
                ];
            }
        }

        if (!empty($tagsToCreate)) {
            Tag::insert($tagsToCreate);
        }

        return Tag::all();
    }

    /**
     * Generate translations in chunks to avoid memory issues.
     *
     * @param int $totalCount
     * @param Collection $languages
     * @param Collection $tags
     * @return void
     */
    private function generateTranslationsInChunks(int $totalCount, Collection $languages, Collection $tags): void
    {
        $chunkSize = min(self::MAX_CHUNK_SIZE, $totalCount);
        $chunks = ceil($totalCount / $chunkSize);

        $this->output->progressStart($chunks);

        for ($i = 0; $i < $chunks; $i++) {
            $currentChunkSize = min($chunkSize, $totalCount - ($i * $chunkSize));
            $this->createTranslationsChunk($currentChunkSize, $languages, $tags);
            $this->output->progressAdvance();
        }

        $this->output->progressFinish();
    }

    /**
     * Create a chunk of translations with associated tags.
     *
     * @param int $chunkSize
     * @param Collection $languages
     * @param Collection $tags
     * @return void
     */
    private function createTranslationsChunk(int $chunkSize, Collection $languages, Collection $tags): void
    {
        $translationData = $this->prepareTranslationData($chunkSize, $languages);
        $tagRelations = $this->prepareTagRelations($translationData['uuids'], $tags);

        $this->saveDataToDatabase($translationData['translations'], $tagRelations);
    }

    /**
     * Prepare translation data for bulk insertion.
     *
     * @param int $chunkSize
     * @param Collection $languages
     * @return array
     */
    private function prepareTranslationData(int $chunkSize, Collection $languages): array
    {
        $uuids = $this->generateUuids($chunkSize);
        $translations = [];

        for ($i = 0; $i < $chunkSize; $i++) {
            $translations[] = [
                'id' => $uuids[$i],
                'key' => 'key_' . uniqid(),
                'value' => 'Sample translation ' . rand(1000, 9999),
                'language_id' => $languages->random()->id,
                'created_at' => now(),
                'updated_at' => now()
            ];
        }

        return [
            'uuids' => $uuids,
            'translations' => $translations
        ];
    }

    /**
     * Generate an array of UUIDs.
     *
     * @param int $count
     * @return array
     */
    private function generateUuids(int $count): array
    {
        $uuids = [];
        for ($i = 0; $i < $count; $i++) {
            $uuids[] = (string) Str::orderedUuid();
        }
        return $uuids;
    }

    /**
     * Prepare tag relations for bulk insertion.
     *
     * @param array $translationIds
     * @param Collection $tags
     * @return array
     */
    private function prepareTagRelations(array $translationIds, Collection $tags): array
    {
        $tagRelations = [];

        foreach ($translationIds as $translationId) {
            $tagCount = rand(1, min(self::MAX_TAGS_PER_TRANSLATION, $tags->count()));
            $randomTags = $tags->random($tagCount)->pluck('id')->unique()->toArray();

            foreach ($randomTags as $tagId) {
                $tagRelations[] = [
                    'translation_id' => $translationId,
                    'tag_id' => $tagId,
                ];
            }
        }

        return $tagRelations;
    }

    /**
     * Save prepared data to the database in a transaction.
     *
     * @param array $translations
     * @param array $tagRelations
     * @return void
     */
    private function saveDataToDatabase(array $translations, array $tagRelations): void
    {
        DB::transaction(function () use ($translations, $tagRelations) {
            Translation::insert($translations);

            if (!empty($tagRelations)) {
                DB::table('translation_tag')->insert($tagRelations);
            }
        });
    }
}
