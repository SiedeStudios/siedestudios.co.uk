<?php

use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ClientController;
use App\Http\Controllers\Admin\ClientProjectController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\MonitorController;
use App\Http\Controllers\Admin\PaymentLogController;
use App\Http\Controllers\Admin\ProjectController;
use App\Http\Controllers\Admin\ProjectImageController;
use App\Http\Controllers\Admin\ServiceController;
use App\Http\Controllers\Admin\SettingsController;
use App\Http\Controllers\Admin\ShareController;
use App\Http\Controllers\Admin\StoreProductController;
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

    Route::resource('clients', ClientController::class)->except(['create', 'edit']);
    Route::post('clients/{client}/invite', [ClientController::class, 'invite'])->name('clients.invite');
    Route::delete('clients/{client}/invite', [ClientController::class, 'revokeInvite'])->name('clients.invite.revoke');
    Route::delete('clients/{client}/users/{user}', [ClientController::class, 'removeUser'])->name('clients.users.destroy');
    Route::post('clients/{client}/files', [ClientController::class, 'upload'])->name('clients.files.store');
    Route::get('client-files/{file}', [ClientController::class, 'download'])->name('client-files.download');
    Route::delete('client-files/{file}', [ClientController::class, 'deleteFile'])->name('client-files.destroy');

    Route::post('clients/{client}/projects', [ClientProjectController::class, 'store'])->name('client-projects.store');
    Route::get('client-projects/{project}', [ClientProjectController::class, 'show'])->name('client-projects.show');
    Route::patch('client-projects/{project}', [ClientProjectController::class, 'update'])->name('client-projects.update');
    Route::delete('client-projects/{project}', [ClientProjectController::class, 'destroy'])->name('client-projects.destroy');
    Route::post('client-projects/{project}/milestones', [ClientProjectController::class, 'addMilestone'])->name('client-projects.milestones.store');
    Route::patch('client-milestones/{milestone}', [ClientProjectController::class, 'updateMilestone'])->name('client-milestones.update');
    Route::delete('client-milestones/{milestone}', [ClientProjectController::class, 'deleteMilestone'])->name('client-milestones.destroy');
    Route::post('client-projects/{project}/updates', [ClientProjectController::class, 'postUpdate'])->name('client-projects.updates.store');
    Route::delete('client-updates/{update}', [ClientProjectController::class, 'deleteUpdate'])->name('client-updates.destroy');
    Route::post('client-projects/{project}/files', [ClientProjectController::class, 'uploadFile'])->name('client-projects.files.store');
    Route::post('client-projects/{project}/testimonial', [ClientProjectController::class, 'requestTestimonial'])->name('client-projects.testimonial');

    Route::resource('invoices', InvoiceController::class);
    Route::patch('invoices/{invoice}/status', [InvoiceController::class, 'status'])->name('invoices.status');
    Route::get('payments', PaymentLogController::class)->name('payments.index');

    Route::get('store', [StoreProductController::class, 'index'])->name('store.index');
    Route::post('store', [StoreProductController::class, 'store'])->name('store.store');
    Route::post('store/sync', [StoreProductController::class, 'sync'])->name('store.sync');
    Route::patch('store/{product}', [StoreProductController::class, 'update'])->name('store.update');
    Route::delete('store/{product}', [StoreProductController::class, 'destroy'])->name('store.destroy');

    Route::get('monitors', [MonitorController::class, 'index'])->name('monitors.index');
    Route::post('monitors', [MonitorController::class, 'store'])->name('monitors.store');
    Route::patch('monitors/{monitor}', [MonitorController::class, 'update'])->name('monitors.update');
    Route::post('monitors/{monitor}/check', [MonitorController::class, 'check'])->name('monitors.check');
    Route::delete('monitors/{monitor}', [MonitorController::class, 'destroy'])->name('monitors.destroy');

    Route::resource('shares', ShareController::class)->except('show');

    Route::resource('messages', MessageController::class)->only(['index', 'show', 'destroy']);
    Route::patch('messages/{message}/read', [MessageController::class, 'toggleRead'])->name('messages.read');

    Route::get('settings/{group?}', [SettingsController::class, 'edit'])->name('settings.edit');
    Route::post('settings/{group}', [SettingsController::class, 'update'])->name('settings.update');
});
