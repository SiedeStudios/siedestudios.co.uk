<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\PaymentLogController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectImageController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ShareController;
use App\Http\Controllers\Admin\TestimonialController;
use Illuminate\Support\Facades\Route;

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', DashboardController::class)->name('dashboard');

    Route::resource('projects', ProjectController::class)->except('show');
    Route::post('projects/reorder', [ProjectController::class, 'reorder'])->name('projects.reorder');
    Route::post('projects/{project}/images', [ProjectImageController::class, 'store'])->name('projects.images.store');
    Route::post('projects/{project}/images/reorder', [ProjectImageController::class, 'reorder'])->name('projects.images.reorder');
    Route::patch('project-images/{image}', [ProjectImageController::class, 'update'])->name('projects.images.update');
    Route::delete('project-images/{image}', [ProjectImageController::class, 'destroy'])->name('projects.images.destroy');

    Route::resource('categories', CategoryController::class)->only(['index', 'store', 'update', 'destroy']);

    Route::resource('services', ServiceController::class)->except('show');
    Route::post('services/reorder', [ServiceController::class, 'reorder'])->name('services.reorder');

    Route::resource('testimonials', TestimonialController::class)->except('show');
    Route::patch('testimonials/{testimonial}/status', [TestimonialController::class, 'status'])->name('testimonials.status');

    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'status'])->name('invoices.status');
    Route::get('payments', PaymentLogController::class)->name('payments.index');

    Route::resource('shares', ShareController::class)->except('show');

    Route::resource('messages', MessageController::class)->only(['index', 'show', 'destroy']);
    Route::patch('messages/{message}/read', [MessageController::class, 'toggleRead'])->name('messages.read');

    Route::get('settings/{group?}', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
});
