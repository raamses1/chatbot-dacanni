<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::prefix('v1')->middleware('throttle:30,1')->group(function () {
    Route::post('/chat',   [ChatbotController::class, 'handle']);
    Route::post('/select', [ChatbotController::class, 'select']);
    Route::post('/rate', [ChatbotController::class, 'rate']);
});

// Ruta de prueba — quitar en producción
Route::get('/test', [ChatbotController::class, 'testWoo']);

