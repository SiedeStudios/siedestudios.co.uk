<?php

use App\Http\Controllers\Portal\PortalAuthController;
use App\Http\Controllers\Portal\PortalController;
use Illuminate\Support\Facades\Route;

Route::prefix('portal')->name('portal.')->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('login', [PortalAuthController::class, 'login'])->name('login');
    });

    // Single-use invite links work whether or not someone is signed in.
    Route::get('invite/{token}', [PortalAuthController::class, 'invite'])->middleware('throttle:public-forms')->name('invite');

    Route::middleware(['auth', 'client'])->group(function () {
        Route::get('/', [PortalController::class, 'dashboard'])->name('dashboard');
        Route::get('projects/{project}', [PortalController::class, 'project'])->name('projects.show');
        Route::post('projects/{project}/updates', [PortalController::class, 'postUpdate'])->middleware('throttle:public-forms')->name('projects.updates');
        Route::post('files', [PortalController::class, 'upload'])->middleware('throttle:public-forms')->name('files.store');
        Route::get('files/{file}', [PortalController::class, 'download'])->name('files.download');
        Route::delete('files/{file}', [PortalController::class, 'deleteFile'])->name('files.destroy');
    });
});
