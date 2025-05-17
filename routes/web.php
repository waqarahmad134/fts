<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\FileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WingController;
use App\Http\Controllers\FileMovementController;


use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Auth;

Route::get('/clear', function () {
    Artisan::call('optimize');
    dd('optimized!');
});

Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    return "All caches have been cleared!";
});

// Main Page Route

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [App\Http\Controllers\Auth\AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [App\Http\Controllers\Auth\AuthController::class, 'login']);
});

Route::middleware('auth')->group(function () {
    Route::get('/logout', [App\Http\Controllers\Auth\AuthController::class, 'logout'])->name('logout');
    
    Route::get('/', [Analytics::class, 'index'])->name('dashboard-analytics');

    // Profile Management
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');

    //Files Management
    Route::get('/files', [FileController::class, 'index'])->name('files.index');
    Route::get('/files/create', [FileController::class, 'create'])->name('files.create');
    Route::post('/files', [FileController::class, 'store'])->name('files.store');
    Route::get('/files/{id}', [FileController::class, 'show'])->name('files.show');
    Route::get('/files/{id}/edit', [FileController::class, 'edit'])->name('files.edit');
    Route::put('/files/{id}', [FileController::class, 'update'])->name('files.update');
    Route::put('/file-statuses', [FileController::class, 'updateStatus'])->name('file-statuses.update');
    Route::delete('/files/{id}', [FileController::class, 'destroy'])->name('files.destroy');


    //File movement
    Route::get('/file-movements', [FileMovementController::class, 'index'])->name('file-movements.index');
    Route::get('/file-movements/create', [FileMovementController::class, 'create'])->name('file-movements.create');
    Route::post('/file-movements', [FileMovementController::class, 'store'])->name('file-movements.store');
    Route::get('/file-movements/{id}', [FileMovementController::class, 'show'])->name('file-movements.show');
    Route::get('/file-movements/{id}/edit', [FileMovementController::class, 'edit'])->name('file-movements.edit');
    Route::put('/file-movements/{id}', [FileMovementController::class, 'update'])->name('file-movements.update');
    Route::delete('/file-movements/{id}', [FileMovementController::class, 'destroy'])->name('file-movements.destroy');


    //Role
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

    // Wings 
    Route::get('/wings', [WingController::class, 'index'])->name('wings.index');
    Route::get('/wings/create', [WingController::class, 'create'])->name('wings.create');
    Route::post('/wings', [WingController::class, 'store'])->name('wings.store');
    Route::get('/wings/{id}', [WingController::class, 'show'])->name('wings.show');
    Route::get('/wings/{id}/edit', [WingController::class, 'edit'])->name('wings.edit');
    Route::put('/wings/{id}', [WingController::class, 'update'])->name('wings.update');
    Route::delete('/wings/{id}', [WingController::class, 'destroy'])->name('wings.destroy');


    //user
    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->name('users.destroy');

});

