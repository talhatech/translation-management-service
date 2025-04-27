<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\LanguageController;
use App\Http\Controllers\Api\TagController;
use App\Http\Controllers\Api\TranslationController;


// Route::get('/user', function (Request $request) {
//     return $request->user();
// })->middleware('auth:sanctum');



Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

// Protected routes
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);

    // Languages
    Route::apiResource('languages', LanguageController::class);

    // Tags
    Route::apiResource('tags', TagController::class);

    // Translations
    Route::apiResource('translations', TranslationController::class);
    Route::get('/export', [TranslationController::class, 'export']);

});
