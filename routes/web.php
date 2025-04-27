<?php

use App\Models\Language;
use Illuminate\Support\Facades\Route;

Route::get('/test', function () {



    Language::create(
        ['code' => 'en', 'name' => 'English']
    );

});
