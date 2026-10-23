<?php

use App\Http\Controllers\AccountController;
use App\Http\Controllers\Admin\AdminController;
use App\Http\Controllers\ApplicationController;
use App\Http\Controllers\AssistantController;
use App\Http\Controllers\Auth\LockController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\CareerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HelpController;
use App\Http\Controllers\JobKitController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicController;
use Illuminate\Support\Facades\Route;

Route::get('/', [PublicController::class, 'home'])->name('home');
Route::get('/pricing', [PublicController::class, 'pricing'])->name('pricing');
Route::get('/privacy', [PublicController::class, 'privacy'])->name('privacy');
Route::get('/terms', [PublicController::class, 'terms'])->name('terms');
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

    Route::get('/plans', [PaymentController::class, 'plans'])->name('plans');
    Route::post('/payments', [PaymentController::class, 'store'])->middleware('throttle:10,1')->name('payments.store');
    Route::get('/payments/callback', [PaymentController::class, 'callback'])->name('payments.callback');
    Route::get('/payments/fake/{paymentId}', [PaymentController::class, 'fake'])->name('payments.fake');

    Route::post('/applications/{id}/report', [HelpController::class, 'reportField'])->whereNumber('id')->middleware('throttle:10,1')->name('applications.report');

    Route::get('/career', [CareerController::class, 'index'])->name('career');
    Route::post('/career', [CareerController::class, 'store'])->name('career.store');
    Route::put('/career/{id}', [CareerController::class, 'update'])->whereNumber('id')->name('career.update');
    Route::delete('/career/{id}', [CareerController::class, 'destroy'])->whereNumber('id')->name('career.destroy');

    Route::get('/jobs', [JobKitController::class, 'index'])->name('jobs.index');
    Route::get('/jobs/new', [JobKitController::class, 'create'])->name('jobs.create');
    Route::post('/jobs', [JobKitController::class, 'store'])->middleware('throttle:6,1')->name('jobs.store');
    Route::get('/jobs/{id}', [JobKitController::class, 'show'])->whereNumber('id')->name('jobs.show');
    Route::put('/jobs/{id}', [JobKitController::class, 'update'])->whereNumber('id')->name('jobs.update');
    Route::get('/jobs/{id}/{part}.pdf', [JobKitController::class, 'pdf'])->whereNumber('id')->name('jobs.pdf');
    Route::delete('/jobs/{id}', [JobKitController::class, 'destroy'])->whereNumber('id')->name('jobs.destroy');

    Route::get('/assistant', [AssistantController::class, 'show'])->name('assistant');
    Route::post('/assistant', [AssistantController::class, 'ask'])->middleware('throttle:20,1')->name('assistant.ask');
    Route::delete('/assistant', [AssistantController::class, 'clear'])->name('assistant.clear');

    Route::get('/help', [HelpController::class, 'index'])->name('help.index');
    Route::post('/help', [HelpController::class, 'store'])->middleware('throttle:5,1')->name('help.store');
    Route::get('/help/{id}', [HelpController::class, 'show'])->whereNumber('id')->name('help.show');
    Route::post('/help/{id}', [HelpController::class, 'reply'])->whereNumber('id')->middleware('throttle:10,1')->name('help.reply');

    Route::get('/settings', [AccountController::class, 'show'])->name('settings');
    Route::delete('/account', [AccountController::class, 'destroy'])->middleware('throttle:5,1')->name('account.destroy');

    Route::prefix('admin')->name('admin.')->middleware('admin')->group(function () {
        Route::get('/', [AdminController::class, 'dashboard'])->name('dashboard');
        Route::get('/templates', [AdminController::class, 'templates'])->name('templates');
        Route::get('/templates/{id}', [AdminController::class, 'template'])->whereNumber('id')->name('template');
        Route::post('/templates/{id}', [AdminController::class, 'saveTemplate'])->whereNumber('id')->name('template.save');
        Route::post('/templates/{id}/publish', [AdminController::class, 'publishTemplate'])->whereNumber('id')->name('template.publish');
        Route::post('/templates/{id}/verify', [AdminController::class, 'verifyTemplate'])->whereNumber('id')->name('template.verify');
        Route::get('/tickets', [AdminController::class, 'tickets'])->name('tickets');
        Route::get('/tickets/{id}', [AdminController::class, 'ticket'])->whereNumber('id')->name('ticket');
        Route::post('/tickets/{id}', [AdminController::class, 'replyTicket'])->whereNumber('id')->name('ticket.reply');
        Route::get('/payments', [AdminController::class, 'payments'])->name('payments');
    });
});
