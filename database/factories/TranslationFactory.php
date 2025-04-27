<?php

namespace Database\Factories;

use App\Models\Language;
use App\Models\Translation;
use Illuminate\Database\Eloquent\Factories\Factory;

class TranslationFactory extends Factory
{
    protected $model = Translation::class;

    public function definition()
    {
        return [
            'key' => 'key_' . $this->faker->unique()->word,
            'value' => $this->faker->sentence(),
            'language_id' => function () {
                return Language::factory()->create()->id;
            },
        ];
    }
}
