<?php

namespace Database\Seeders;

use App\Models\Language;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run()
    {
        // Create admin user
        User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        // Create default languages
        $languages = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
            ['code' => 'de', 'name' => 'German'],
            ['code' => 'it', 'name' => 'Italian'],
        ];

        foreach ($languages as $language) {
            Language::create($language);
        }

        // Create default tags
        $tags = ['mobile', 'desktop', 'web', 'admin', 'user', 'public', 'private'];

        foreach ($tags as $tagName) {
            Tag::create(['name' => $tagName]);
        }
    }
}
