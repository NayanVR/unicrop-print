<?php

use App\Http\Controllers\Auth\PasswordResetRequestController;
use App\Http\Controllers\CuttingController;
use App\Http\Controllers\DispatchController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\FileController;
use App\Http\Controllers\PrinterController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\RecordController;
use App\Http\Controllers\SettingsController;
use App\Http\Controllers\UploaderController;
use App\Http\Controllers\UserController;
use App\Support\Permission;
use Illuminate\Support\Facades\Route;

Route::redirect('/', '/dashboard');

Route::middleware('guest')->group(function () {
    Route::get('/forgot-password', [PasswordResetRequestController::class, 'create'])->name('password.request');
    Route::post('/forgot-password', [PasswordResetRequestController::class, 'store'])->name('password.email');
});

Route::get('/share/jobs/{printJob}/file', [FileController::class, 'showPublic'])
    ->name('jobs.public-file')
    ->middleware('signed');

Route::middleware('auth')->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    Route::get('/jobs/{printJob}/file', [FileController::class, 'show'])->name('jobs.file');

    Route::middleware('permission:'.Permission::UPLOAD_DESIGN)->group(function () {
        Route::get('/uploader', [UploaderController::class, 'create'])->name('uploader.create');
        Route::post('/uploader', [UploaderController::class, 'store'])->name('uploader.store');
    });

    Route::middleware('permission:'.Permission::PRINT_STATION)->group(function () {
        Route::get('/printer', [PrinterController::class, 'index'])->name('printer.index');
        Route::patch('/printer/{printJob}', [PrinterController::class, 'update'])->name('printer.update');
    });

    Route::middleware('permission:'.Permission::CUTTING_STATION)->group(function () {
        Route::get('/cutting', [CuttingController::class, 'index'])->name('cutting.index');
        Route::patch('/cutting/{printJob}', [CuttingController::class, 'update'])->name('cutting.update');
    });

    Route::middleware('permission:'.Permission::DISPATCH)->group(function () {
        Route::get('/dispatch', [DispatchController::class, 'index'])->name('dispatch.index');
        Route::post('/dispatch/bulk', [DispatchController::class, 'bulkDispatch'])->name('dispatch.bulk');
    });

    Route::middleware('permission:'.Permission::BILLING_LOGS)->group(function () {
        Route::get('/records', [RecordController::class, 'index'])->name('records.index');
        Route::get('/records/pdf', [RecordController::class, 'pdf'])->name('records.pdf');
    });

    // Settings view + rate editing: admin OR print_station users (print_station users see only their own stations)
    Route::middleware('permission:'.Permission::SYSTEM_SETTINGS.','.Permission::PRINT_STATION)->group(function () {
        Route::get('/settings', [SettingsController::class, 'index'])->name('settings.index');
        Route::patch('/settings/station-rates', [SettingsController::class, 'updateStationRates'])->name('settings.station-rates.update');
        Route::patch('/settings/station-cutting-rates', [SettingsController::class, 'updateStationCuttingRates'])->name('settings.station-cutting-rates.update');
        Route::patch('/settings/station-lamination-rates', [SettingsController::class, 'updateStationLaminationRates'])->name('settings.station-lamination-rates.update');
    });

    // Full settings management: system_settings permission (admin or granted)
    Route::middleware('permission:'.Permission::SYSTEM_SETTINGS)->group(function () {
        Route::post('/settings/sizes', [SettingsController::class, 'storeSize'])->name('settings.sizes.store');
        Route::patch('/settings/sizes/{size}', [SettingsController::class, 'updateSize'])->name('settings.sizes.update');
        Route::delete('/settings/sizes/{size}', [SettingsController::class, 'destroySize'])->name('settings.sizes.destroy');
        Route::patch('/settings/sizes/{size}/default', [SettingsController::class, 'setDefaultSize'])->name('settings.sizes.default');
        Route::patch('/settings/cutting-rate', [SettingsController::class, 'updateCuttingRate'])->name('settings.cutting-rate.update');
        Route::patch('/settings/stations/{station}/default', [SettingsController::class, 'setDefaultStation'])->name('settings.stations.default');
        Route::patch('/settings/stations/{station}/cutting', [SettingsController::class, 'toggleStationCutting'])->name('settings.stations.cutting');
        Route::patch('/settings/cutting-types/{cuttingType}/default', [SettingsController::class, 'setDefaultCuttingType'])->name('settings.cutting-types.default');
    });

    Route::middleware('admin')->group(function () {
        Route::get('/users', [UserController::class, 'index'])->name('users.index');
        Route::post('/users', [UserController::class, 'store'])->name('users.store');
        Route::patch('/users/{user}/info', [UserController::class, 'updateInfo'])->name('users.info.update');
        Route::patch('/users/{user}/access', [UserController::class, 'updateAccess'])->name('users.access.update');
        Route::patch('/users/{user}/password', [UserController::class, 'resetPassword'])->name('users.password.update');
        Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');

        Route::post('/settings/stations', [SettingsController::class, 'storeStation'])->name('settings.stations.store');
        Route::delete('/settings/stations/{station}', [SettingsController::class, 'destroyStation'])->name('settings.stations.destroy');

        Route::post('/settings/cutting-types', [SettingsController::class, 'storeCuttingType'])->name('settings.cutting-types.store');
        Route::delete('/settings/cutting-types/{cuttingType}', [SettingsController::class, 'destroyCuttingType'])->name('settings.cutting-types.destroy');
        Route::post('/settings/lamination-types', [SettingsController::class, 'storeLaminationType'])->name('settings.lamination-types.store');
        Route::delete('/settings/lamination-types/{laminationType}', [SettingsController::class, 'destroyLaminationType'])->name('settings.lamination-types.destroy');
        Route::patch('/settings/lamination-types/{laminationType}/default', [SettingsController::class, 'setDefaultLaminationType'])->name('settings.lamination-types.default');
    });

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
