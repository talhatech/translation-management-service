<?php

namespace Database\Factories;

use App\Models\Tag;
use Illuminate\Database\Eloquent\Factories\Factory;

class TagFactory extends Factory
{
    protected $model = Tag::class;

    public function definition()
    {
        $tags = ['mobile', 'desktop', 'web', 'admin', 'user', 'public', 'private'];

        return [
            'name' => $this->faker->unique()->randomElement($tags),
        ];
    }
}
