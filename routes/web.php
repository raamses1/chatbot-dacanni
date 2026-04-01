<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\AuthController;

// Login
Route::get('/login',   [AuthController::class, 'showLogin'])->name('login');
Route::post('/login',  [AuthController::class, 'login'])->name('login.post');
Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// Rutas protegidas
Route::middleware('auth.admin')->group(function () {
    Route::get('/', [AuthController::class, 'showHome'])->name('home');

    Route::prefix('admin')->group(function () {
        Route::get('/',                    [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/conversaciones',      [AdminController::class, 'conversations'])->name('admin.conversations');
        Route::get('/conversaciones/{id}', [AdminController::class, 'conversationDetail'])->name('admin.conversation.detail');
    });
});