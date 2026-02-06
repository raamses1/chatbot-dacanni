<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\ChatbotController;

Route::post('/chat', [ChatbotController::class, 'handle']);
Route::get('/test', [ChatbotController::class, 'testWoo']);
