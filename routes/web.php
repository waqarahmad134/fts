<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\dashboard\Analytics;
use App\Http\Controllers\FileController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WingController;
use App\Http\Controllers\FileMovementController;
use App\Http\Controllers\LogController;


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


Route::get('/delete-db', function () {
    DB::statement('SET FOREIGN_KEY_CHECKS = 0');
    $tables = DB::select('SHOW TABLES');
    $dbName = 'Tables_in_' . DB::getDatabaseName();
    foreach ($tables as $table) {
        Schema::drop($table->$dbName);
    }

    DB::statement('SET FOREIGN_KEY_CHECKS = 1');
    return 'All tables dropped successfully!';
});

Route::get('/migrations', function () {
    Artisan::call('migrate', ['--force' => true]);
    return 'Migrations executed successfully!';
});

Route::get('/seed', function () {
    Artisan::call('db:seed', ['--force' => true]);
    return 'Database seeded successfully!';
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
    Route::get('/profile', [App\Http\Controllers\ProfileController::class, 'edit'])->middleware('permission:view_profile')->name('profile.edit');
    Route::put('/profile', [App\Http\Controllers\ProfileController::class, 'update'])->middleware('permission:edit_profile')->name('profile.update');

    //Files Management
    Route::get('/files', [FileController::class, 'index'])->middleware('permission:view_files')->name('files.index');
    Route::get('/files/create', [FileController::class, 'create'])->middleware('permission:create_files')->name('files.create');
    Route::post('/files', [FileController::class, 'store'])->middleware('permission:create_files')->name('files.store');
    Route::get('/files/{id}', [FileController::class, 'show'])->middleware('permission:view_files')->name('files.show');
    Route::get('/files/{id}/edit', [FileController::class, 'edit'])->middleware('permission:edit_files')->name('files.edit');
    Route::put('/files/{id}', [FileController::class, 'update'])->middleware('permission:edit_files')->name('files.update');
    Route::match(['put', 'post'], '/file-statuses', [FileController::class, 'updateStatus'])->middleware('permission:edit_file_statuses')->name('file-statuses.update');
    Route::delete('/files/{id}', [FileController::class, 'destroy'])->middleware('permission:delete_files')->name('files.destroy');
    Route::get('/history', [FileController::class, 'file_history'])->middleware('permission:view_files')->name('files.history');

    //File movement
    Route::get('/file-movements', [FileMovementController::class, 'index'])->name('file-movements.index');
    Route::get('/file-movements/create', [FileMovementController::class, 'create'])->name('file-movements.create');
    Route::post('/file-movements', [FileMovementController::class, 'store'])->name('file-movements.store');
    Route::get('/file-movements/{id}', [FileMovementController::class, 'show'])->name('file-movements.show');
    Route::get('/file-movements/{id}/edit', [FileMovementController::class, 'edit'])->name('file-movements.edit');
    Route::put('/file-movements/{id}', [FileMovementController::class, 'update'])->name('file-movements.update');
    Route::delete('/file-movements/{id}', [FileMovementController::class, 'destroy'])->name('file-movements.destroy');
    Route::get('/file-movements/scanner', [FileMovementController::class, 'scanner'])->name('file-movements.scanner');
    Route::get('/file-movements/scan/{fileId}', [FileMovementController::class, 'scan'])->name('file-movements.scan');

    //QR Code Generation
    Route::get('/files/{id}/qr-code', [FileController::class, 'generateQrCode'])->middleware('permission:view_files')->name('files.qr-code');

    //Print File with QR Code
    Route::get('/files/{id}/print', [FileController::class, 'print'])->middleware('permission:view_files')->name('files.print');


    //Role
    Route::get('/roles', [RoleController::class, 'index'])->name('roles.index');
    Route::get('/roles/create', [RoleController::class, 'create'])->name('roles.create');
    Route::post('/roles', [RoleController::class, 'store'])->name('roles.store');
    Route::get('/roles/{id}', [RoleController::class, 'show'])->name('roles.show');
    Route::get('/roles/{id}/edit', [RoleController::class, 'edit'])->name('roles.edit');
    Route::put('/roles/{id}', [RoleController::class, 'update'])->name('roles.update');
    Route::delete('/roles/{id}', [RoleController::class, 'destroy'])->name('roles.destroy');

    Route::get('/roles/{role}/permissions', [RoleController::class, 'getPermissions'])->name('roles.permissions');
    Route::post('/roles/{role}/permissions', [RoleController::class, 'updatePermissions'])->name('roles.permissions.update');

    // Wings 
    Route::get('/wings', [WingController::class, 'index'])->name('wings.index');
    Route::get('/wings/create', [WingController::class, 'create'])->name('wings.create');
    Route::post('/wings', [WingController::class, 'store'])->name('wings.store');
    Route::get('/wings/{id}', [WingController::class, 'show'])->name('wings.show');
    Route::get('/wings/{id}/edit', [WingController::class, 'edit'])->name('wings.edit');
    Route::put('/wings/{id}', [WingController::class, 'update'])->name('wings.update');
    Route::delete('/wings/{id}', [WingController::class, 'destroy'])->name('wings.destroy');


    //user
    Route::get('/users', [UserController::class, 'index'])->middleware('permission:view_users')->name('users.index');
    Route::get('/users/create', [UserController::class, 'create'])->middleware('permission:create_users')->name('users.create');
    Route::post('/users', [UserController::class, 'store'])->middleware('permission:create_users')->name('users.store');
    Route::get('/users/{id}', [UserController::class, 'show'])->middleware('permission:view_users')->name('users.show');
    Route::get('/users/{id}/edit', [UserController::class, 'edit'])->middleware('permission:edit_users')->name('users.edit');
    Route::put('/users/{id}', [UserController::class, 'update'])->middleware('permission:edit_users')->name('users.update');
    Route::delete('/users/{id}', [UserController::class, 'destroy'])->middleware('permission:delete_users')->name('users.destroy');

    // Error Logs (Admin only)
    Route::get('/logs', [LogController::class, 'index'])->name('logs.index');
    Route::post('/logs/clear', [LogController::class, 'clear'])->name('logs.clear');

});
