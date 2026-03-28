<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;

Route::get('/', function () {
    return view('welcome');
});

Route::prefix('admin')->group(function () {
    Route::get('/',             [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/conversaciones', [AdminController::class, 'conversations'])->name('admin.conversations');
    Route::get('/conversaciones/{id}', [AdminController::class, 'conversationDetail'])->name('admin.conversation.detail');
});
