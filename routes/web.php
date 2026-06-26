<?php

use App\Http\Controllers\CuttingController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UploaderController;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::middleware('role:admin,uploader')->group(function () {
        Route::get('/uploader', [UploaderController::class, 'create'])->name('uploader.create');
        Route::post('/uploader', [UploaderController::class, 'store'])->name('uploader.store');
    });

    Route::middleware('role:admin,printer')->group(function () {
        Route::get('/printer', [PrinterController::class, 'index'])->name('printer.index');
        Route::patch('/printer/{printJob}', [PrinterController::class, 'update'])->name('printer.update');

        Route::get('/cutting', [CuttingController::class, 'index'])->name('cutting.index');
        Route::patch('/cutting/{printJob}', [CuttingController::class, 'update'])->name('cutting.update');
    });

    Route::get('/records', [RecordController::class, 'index'])->name('records.index');

    Route::middleware('role:admin')->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::post('/settings/sizes', [SettingsController::class, 'storeSize'])->name('settings.sizes.store');
        Route::patch('/settings/sizes/{size}', [SettingsController::class, 'updateSize'])->name('settings.sizes.update');
        Route::delete('/settings/sizes/{size}', [SettingsController::class, 'destroySize'])->name('settings.sizes.destroy');
        Route::patch('/settings/sizes/{size}/default', [SettingsController::class, 'setDefaultSize'])->name('settings.sizes.default');
        Route::patch('/settings/cutting-rate', [SettingsController::class, 'updateCuttingRate'])->name('settings.cutting-rate.update');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
