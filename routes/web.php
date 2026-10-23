<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\Auth\LockController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');
Route::post('/locale', [AccountController::class, 'locale'])->name('locale');

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showMobile'])->name('login');
    Route::post('/login', [LoginController::class, 'sendCode'])->middleware('throttle:10,1');
    Route::get('/login/verify', [LoginController::class, 'showVerify'])->name('login.verify');
    Route::post('/login/verify', [LoginController::class, 'verify'])->middleware('throttle:10,1');
    Route::post('/login/resend', [LoginController::class, 'resend'])->middleware('throttle:3,1')->name('login.resend');
    Route::get('/login/pin', [LoginController::class, 'showPin'])->name('login.pin');
    Route::post('/login/pin', [LoginController::class, 'submitPin'])->middleware('throttle:10,1');
});

Route::middleware(['auth', 'idle.lock'])->group(function () {
    Route::get('/lock', [LockController::class, 'show'])->name('lock');
    Route::post('/lock', [LockController::class, 'lockNow'])->name('lock.now');
    Route::post('/unlock', [LockController::class, 'unlock'])->middleware('throttle:10,1')->name('unlock');
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/dashboard', DashboardController::class)->name('dashboard');

    Route::post('/pin/confirm', [LockController::class, 'confirm'])->middleware('throttle:10,1')->name('pin.confirm');

    Route::get('/documents', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/documents/new', [DocumentController::class, 'create'])->name('documents.create');
    Route::post('/documents', [DocumentController::class, 'store'])->name('documents.store');
    Route::get('/documents/{id}', [DocumentController::class, 'show'])->whereNumber('id')->name('documents.show');
    Route::get('/documents/{id}/file', [DocumentController::class, 'file'])->whereNumber('id')->name('documents.file');
    Route::put('/documents/{id}/fields', [DocumentController::class, 'updateFields'])->whereNumber('id')->name('documents.fields');
    Route::delete('/documents/{id}', [DocumentController::class, 'destroy'])->whereNumber('id')->name('documents.destroy');

    Route::get('/profile', [ProfileController::class, 'index'])->name('profile');
    Route::put('/profile/{key}', [ProfileController::class, 'update'])->name('profile.update');

    Route::get('/forms', [ApplicationController::class, 'index'])->name('forms.index');
    Route::post('/forms/{slug}', [ApplicationController::class, 'start'])->name('forms.start');
    Route::get('/applications/{id}', [ApplicationController::class, 'show'])->whereNumber('id')->name('applications.show');
    Route::get('/applications/{id}/print', [ApplicationController::class, 'print'])->whereNumber('id')->name('applications.print');
    Route::post('/applications/{id}/copied', [ApplicationController::class, 'copied'])->whereNumber('id')->name('applications.copied');
    Route::delete('/applications/{id}/copied', [ApplicationController::class, 'resetCopied'])->whereNumber('id')->name('applications.copied.reset');
    Route::post('/applications/{id}/complete', [ApplicationController::class, 'complete'])->whereNumber('id')->name('applications.complete');
    Route::delete('/applications/{id}', [ApplicationController::class, 'destroy'])->whereNumber('id')->name('applications.destroy');

    Route::get('/settings', [AccountController::class, 'show'])->name('settings');
    Route::delete('/account', [AccountController::class, 'destroy'])->middleware('throttle:5,1')->name('account.destroy');
});
