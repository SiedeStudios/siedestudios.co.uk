<?php

use App\Http\Controllers\Auth\DevLoginController;
use App\Http\Controllers\Auth\DiscordAuthController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\PaymentController;
use App\Http\Controllers\PayPalIpnController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\ShareController;
use App\Http\Controllers\StatusController;
use App\Http\Controllers\StoreController;
use App\Http\Controllers\TestimonialSubmissionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

Route::get('/projects', [ProjectController::class, 'index'])->name('projects.index');
Route::get('/projects/{project:slug}', [ProjectController::class, 'show'])->name('projects.show');

Route::get('/services', ServiceController::class)->name('services');

Route::get('/store', StoreController::class)->name('store');
Route::get('/status', StatusController::class)->name('status');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:contact')->name('contact.store');

// Invoices & PayPal
Route::get('/pay', [PaymentController::class, 'lookup'])->name('pay.lookup');
Route::post('/pay', [PaymentController::class, 'find'])->middleware('throttle:public-forms')->name('pay.find');
Route::get('/pay/{invoice:reference}', [PaymentController::class, 'show'])->name('pay.show');
Route::post('/paypal/ipn', PayPalIpnController::class)->name('paypal.ipn');

// Testimonial invites
Route::get('/testimonial/{token}', [TestimonialSubmissionController::class, 'show'])->name('testimonial.show');
Route::post('/testimonial/{token}', [TestimonialSubmissionController::class, 'store'])->middleware('throttle:public-forms')->name('testimonial.store');

// Hidden pastebin
Route::get('/s/{share:slug}', [ShareController::class, 'show'])->name('share.show');
Route::get('/s/{share:slug}/raw', [ShareController::class, 'raw'])->name('share.raw');
Route::post('/s/{share:slug}/unlock', [ShareController::class, 'unlock'])->middleware('throttle:public-forms')->name('share.unlock');

// Discord-only authentication
Route::middleware('guest')->group(function () {
    Route::get('/admin/login', [DiscordAuthController::class, 'login'])->name('login');
    Route::get('/auth/discord', [DiscordAuthController::class, 'redirect'])->middleware('throttle:public-forms')->name('auth.discord');
    Route::get('/auth/discord/callback', [DiscordAuthController::class, 'callback'])->name('auth.discord.callback');
});

// Demo admin sign-in — only exists in local/Codespaces environments with DEV_LOGIN=true.
if (app()->environment('local') && config('app.dev_login')) {
    Route::get('/dev/login', DevLoginController::class)->middleware('guest')->name('dev.login');
}

Route::post('/logout', [DiscordAuthController::class, 'logout'])->middleware('auth')->name('logout');
