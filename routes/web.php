<?php

use App\Http\Controllers\Admin\AuthController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\GeneratedLinkedinPostController;
use App\Http\Controllers\Admin\LinkedinAccountController;
use App\Http\Controllers\Admin\LinkedinPostController;
use App\Http\Controllers\Admin\LinkedinPostLogController;
use Illuminate\Support\Facades\Route;

Route::middleware('guest')->group(function (): void {
    Route::get('/', [AuthController::class, 'showLogin'])->name('login');
    Route::get('/login', [AuthController::class, 'showLogin']);
    Route::post('/login', [AuthController::class, 'login'])->name('login.store');
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register'])->name('register.store');
    Route::get('/forgot-password', [AuthController::class, 'forgotPassword'])->name('password.request');
    Route::post('/forgot-password', [AuthController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reset-password/{token}', [AuthController::class, 'resetPassword'])->name('password.reset');
    Route::post('/reset-password', [AuthController::class, 'updatePassword'])->name('password.update');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::get('/linkedin/account', [LinkedinAccountController::class, 'edit'])->name('linkedin.account.edit');
    Route::put('/linkedin/account', [LinkedinAccountController::class, 'update'])->name('linkedin.account.update');
    Route::get('/linkedin/connect', [LinkedinAccountController::class, 'redirect'])->name('linkedin.redirect');
    Route::get('/linkedin/callback', [LinkedinAccountController::class, 'callback'])->name('linkedin.callback');
    Route::post('/linkedin/account/{account}/disconnect', [LinkedinAccountController::class, 'disconnect'])->name('linkedin.account.disconnect');

    Route::get('/posts/generate', [GeneratedLinkedinPostController::class, 'create'])->name('posts.generate');
    Route::post('/posts/generate', [GeneratedLinkedinPostController::class, 'store'])->name('posts.generate.store');
    Route::get('/posts/calendar', [LinkedinPostController::class, 'calendar'])->name('posts.calendar');
    Route::get('/posts/failed', [LinkedinPostController::class, 'failed'])->name('posts.failed');
    Route::post('/posts/{post}/retry', [LinkedinPostController::class, 'retry'])->name('posts.retry');
    Route::resource('posts', LinkedinPostController::class);

    Route::get('/logs', [LinkedinPostLogController::class, 'index'])->name('logs.index');
});
