<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\PortfolioController;
use App\Http\Controllers\Admin\TestimonialController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\SettingsController;

/*
|--------------------------------------------------------------------------
| Admin Routes
|--------------------------------------------------------------------------
|
| These routes are prefixed with 'admin' and are protected by the auth
| middleware plus a check that the user is an admin.
|
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    // Dashboard
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    
    // Portfolio Management
    Route::resource('portfolio', PortfolioController::class);
    Route::post('portfolio/update-order', [PortfolioController::class, 'updateOrder'])->name('portfolio.updateOrder');
    Route::get('portfolio/{portfolio}/status/{status}', [PortfolioController::class, 'changeStatus'])->name('portfolio.changeStatus');
    
    // Testimonial Management
    Route::resource('testimonials', TestimonialController::class);
    Route::post('testimonials/update-order', [TestimonialController::class, 'updateOrder'])->name('testimonials.updateOrder');
    Route::patch('testimonials/{testimonial}/toggle-status', [TestimonialController::class, 'toggleStatus'])->name('testimonials.toggleStatus');
    
    // Message Management
    Route::resource('messages', MessageController::class)->only(['index', 'show', 'destroy']);
    Route::post('messages/{message}/reply', [MessageController::class, 'reply'])->name('messages.reply');
    Route::patch('messages/{message}/mark-as-read', [MessageController::class, 'markAsRead'])->name('messages.markAsRead');
    Route::post('messages/bulk-mark-as-read', [MessageController::class, 'bulkMarkAsRead'])->name('messages.bulkMarkAsRead');
    Route::post('messages/bulk-delete', [MessageController::class, 'bulkDelete'])->name('messages.bulkDelete');
    
    // Settings
    Route::get('settings', [SettingsController::class, 'index'])->name('settings');
    Route::post('settings', [SettingsController::class, 'update'])->name('settings.update');
});