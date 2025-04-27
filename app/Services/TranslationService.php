<?php
namespace App\Services;

use App\Interfaces\TranslationServiceInterface;
use App\Models\Language;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;

class TranslationService implements TranslationServiceInterface
{
    protected $cacheService;

    public function __construct(CacheService $cacheService = null)
    {
        $this->cacheService = $cacheService ?? new CacheService();
    }

    public function getAllLanguages()
    {
        return $this->cacheService->remember('languages', function () {
            return Language::all();
        });
    }

    public function getLanguageByCode(string $code)
    {
        return $this->cacheService->remember("language:{$code}", function () use ($code) {
            return Language::where('code', $code)->first();
        });
    }

    public function createTranslation(array $data)
    {
        $translation = DB::transaction(function () use ($data) {
            $translation = Translation::create([
                'key' => $data['key'],
                'value' => $data['value'],
                'language_id' => $data['language_id'],
            ]);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $tagIds = $this->processTagsArray($data['tags']);
                $translation->tags()->sync($tagIds);
            }

            return $translation;
        });

        // Get the language code for cache clearing
        $languageCode = Language::find($translation->language_id)->code;
        $this->cacheService->clearTranslationCache($languageCode);

        return $translation;
    }

    public function updateTranslation(Translation $translation, array $data)
    {
        $oldLanguageCode = $translation->language->code;

        $updated = DB::transaction(function () use ($translation, $data) {
            $translation->update([
                'key' => $data['key'] ?? $translation->key,
                'value' => $data['value'] ?? $translation->value,
                'language_id' => $data['language_id'] ?? $translation->language_id,
            ]);

            if (isset($data['tags']) && is_array($data['tags'])) {
                $tagIds = $this->processTagsArray($data['tags']);
                $translation->tags()->sync($tagIds);
            }

            return $translation;
        });

        // Clear cache for both old and potentially new language codes
        $this->cacheService->clearTranslationCache($oldLanguageCode);
        if (isset($data['language_id']) && $data['language_id'] != $translation->language_id) {
            $newLanguageCode = Language::find($data['language_id'])->code;
            $this->cacheService->clearTranslationCache($newLanguageCode);
        }

        return $updated;
    }

    public function deleteTranslation(Translation $translation)
    {
        $languageCode = $translation->language->code;

        $result = DB::transaction(function () use ($translation) {
            return $translation->delete();
        });

        $this->cacheService->clearTranslationCache($languageCode);

        return $result;
    }

    public function searchTranslations(array $filters)
    {
        // Start with a query builder with eager loading
        $query = Translation::with(['language', 'tags']);

        // If we're searching by language_code, join directly rather than using whereHas
        if (isset($filters['language_code'])) {
            $query->join('languages', 'translations.language_id', '=', 'languages.id')
                ->where('languages.code', $filters['language_code'])
                ->select('translations.*'); // Select only from translations to avoid ambiguity
        }

        if (isset($filters['key'])) {
            $query->where('key', 'like', '%' . $filters['key'] . '%');
        }

        if (isset($filters['value'])) {
            $query->where('value', 'like', '%' . $filters['value'] . '%');
        }

        if (isset($filters['language_id'])) {
            $query->where('language_id', $filters['language_id']);
        }

        // For tag filtering, use a more efficient query with whereHas
        if (isset($filters['tag'])) {
            $query->whereHas('tags', function ($subquery) use ($filters) {
                $subquery->where('name', $filters['tag']);
            });
        }

        return $query->paginate($filters['per_page'] ?? 15);
    }

    public function getTranslationsForLanguage(string $languageCode, array $tags = [])
    {
        // First check if the language exists
        $language = $this->getLanguageByCode($languageCode);
        if (!$language || !$language->is_active) {
            return [];
        }

        $cacheKey = "translations:{$languageCode}";

        if (!empty($tags)) {
            sort($tags); // Sort tags for consistent cache keys
            $cacheKey .= ':' . implode(',', $tags);
        }

        return $this->cacheService->remember($cacheKey, function () use ($languageCode, $tags, $language) {
            // Start with a base query
            $query = Translation::select('translations.key', 'translations.value')
                ->where('language_id', $language->id);

            // If tags are specified, filter by them efficiently
            if (!empty($tags)) {
                // Use a more efficient tag filtering approach
                $tagCount = count($tags);

                // This ensures the translation has exactly these tags (no more, no less)
                $query->whereHas('tags', function ($subquery) use ($tags) {
                    $subquery->whereIn('name', $tags);
                }, '=', $tagCount);

                $query->whereDoesntHave('tags', function ($subquery) use ($tags) {
                    $subquery->whereNotIn('name', $tags);
                });
            }

            // Execute query and format results
            $translations = $query->get();

            return $translations->pluck('value', 'key')->toArray();
        });
    }

    public function getAllTags()
    {
        return $this->cacheService->remember('tags', function () {
            return Tag::all();
        });
    }

    private function processTagsArray(array $tags): array
    {
        $tagIds = [];

        foreach ($tags as $tagName) {
            $tag = Tag::firstOrCreate(['name' => $tagName]);
            $tagIds[] = $tag->id;
        }

        return $tagIds;
    }
}
