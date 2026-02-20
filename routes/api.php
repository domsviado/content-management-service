<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ContentController;

Route::prefix('v1')->group(function () {
    Route::prefix("/auth")->group(function () {
        Route::post('/signup', [\App\Http\Controllers\Api\AuthController::class, 'signup']);
        Route::post('/login', [\App\Http\Controllers\Api\AuthController::class, 'login']);
        Route::post('/logout', [\App\Http\Controllers\Api\AuthController::class, 'logout'])->middleware('auth:sanctum');
    });
    Route::prefix("content")->group(function () {
        Route::middleware('auth:sanctum')->group(function () {
            Route::get('/search', [ContentController::class, 'search']);
            Route::get('/export/{locale}', [ContentController::class, 'export']);
            Route::get('/detail/{id}', [ContentController::class, 'show']);

            Route::post('/', [ContentController::class, 'store']);
        });

        Route::get('/{locale}', [ContentController::class, 'index']);

    });

});