<?php

namespace App\Services;

use App\Models\Tag;
use Illuminate\Support\Facades\Cache;

class CacheService
{
    private $cacheTime = 60 * 24; // 24 hours in minutes

    public function remember(string $key, callable $callback)
    {
        return Cache::remember($key, $this->cacheTime, $callback);
    }

    public function forget(string $key): void
    {
        Cache::forget($key);
    }

    public function clearTranslationCache(string $languageCode): void
    {
        // Clear the base cache
        $this->forget("translations:{$languageCode}");

        // Clear tag-based caches
        $allTags = Tag::pluck('name')->toArray();
        $this->clearCombinationCaches($languageCode, $allTags);
    }

    private function clearCombinationCaches(string $languageCode, array $tags, $prefix = ''): void
    {
        if (!empty($prefix)) {
            $this->forget("translations:{$languageCode}:{$prefix}");
        }

        if (count($tags) > 0 && strlen($prefix) < 100) {
            foreach ($tags as $i => $tag) {
                $newPrefix = empty($prefix) ? $tag : $prefix . ',' . $tag;
                $remainingTags = array_slice($tags, $i + 1);

                $this->forget("translations:{$languageCode}:{$newPrefix}");
                $this->clearCombinationCaches($languageCode, $remainingTags, $newPrefix);
            }
        }
    }
}
