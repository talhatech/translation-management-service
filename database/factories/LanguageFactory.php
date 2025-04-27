<?php

namespace Database\Factories;

use App\Models\Language;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Factories\Factory;

class LanguageFactory extends Factory
{
    protected $model = Language::class;

    public function definition()
    {
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
        ];

        $language = $this->faker->unique()->randomElement($languages);

        return [
            'id' => Str::orderedUuid(),
            'code' => $language['code'],
            'name' => $language['name'],
            'is_active' => true,
        ];
    }
}
