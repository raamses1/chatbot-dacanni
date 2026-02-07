<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::post('/chat', [ChatbotController::class, 'handle']);
Route::get('/test', [ChatbotController::class, 'testWoo']);
Route::prefix('v1')->group(function () {
    Route::middleware('throttle:30,1')->post('/chat', [ChatbotController::class, 'chat']);
});
