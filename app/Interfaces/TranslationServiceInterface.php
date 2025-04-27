<?php

namespace App\Interfaces;

use App\Models\Language;
use App\Models\Translation;

interface TranslationServiceInterface
{
    public function getAllLanguages();
    public function getLanguageByCode(string $code);
    public function createTranslation(array $data);
    public function updateTranslation(Translation $translation, array $data);
    public function deleteTranslation(Translation $translation);
    public function searchTranslations(array $filters);
    public function getTranslationsForLanguage(string $languageCode, array $tags = []);
    public function getAllTags();
}
