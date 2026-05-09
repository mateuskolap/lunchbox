<?php

use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::inertia('dashboard', 'Dashboard')->name('dashboard');

    // User routes
    Route::prefix('usuarios')->name('users.')->group(function () {
        Route::get('/', [UserController::class, 'index'])->name('index');
        Route::post('/', [UserController::class, 'store'])->name('store');
        Route::put('/{user}', [UserController::class, 'update'])->name('update');
        Route::delete('/{user}', [UserController::class, 'destroy'])->name('destroy');
    });

    // Products routes
    Route::prefix('/produtos')->name('products.')->group(function () {
        Route::middleware('can:products.index')->get('/', [ProductController::class, 'index'])->name('index');
        Route::middleware('can:products.store')->post('/', [ProductController::class, 'store'])->name('store');
        Route::middleware('can:products.update')->put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::middleware('can:products.destroy')->delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });
});

require __DIR__ . '/settings.php';
