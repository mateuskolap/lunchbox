<?php

use App\Http\Controllers\CustomerController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;
use Laravel\Fortify\Features;

Route::inertia('/', 'Welcome', [
    'canRegister' => Features::enabled(Features::registration()),
])->name('home');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('dashboard', function () {
        if (auth()->user()->can('orders.index')) {
            return redirect()->route('orders.today');
        }

        return Inertia\Inertia::render('Dashboard');
    })->name('dashboard');

    // User routes
    Route::prefix('/usuarios')->name('users.')->group(function () {
        Route::middleware('can:users.index')->get('/', [UserController::class, 'index'])->name('index');
        Route::middleware('can:users.store')->post('/', [UserController::class, 'store'])->name('store');

        Route::prefix('/{user}')->group(function () {
            Route::middleware('can:users.update')->put('/', [UserController::class, 'update'])->name('update');
            Route::middleware('can:users.destroy')->delete('/', [UserController::class, 'destroy'])->name('destroy');

            Route::prefix('/papeis')->name('roles.')->group(function () {
                Route::middleware('can:roles.add')->post('/', [UserController::class, 'addRoles'])->name('add-roles');
                Route::middleware('can:roles.remove')->delete('/{role}', [UserController::class, 'removeRole'])->name('remove-role');
            });
        });
    });

    Route::prefix('/papeis')->name('roles.')->group(function () {
        Route::middleware('can:roles.index')->get('/', [RoleController::class, 'index'])->name('index');
        Route::middleware('can:roles.store')->post('/', [RoleController::class, 'store'])->name('store');
        Route::middleware('can:roles.update')->put('/{role}', [RoleController::class, 'update'])->name('update');
        Route::middleware('can:roles.destroy')->delete('/{role}', [RoleController::class, 'destroy'])->name('destroy');
    });

    // Products routes
    Route::prefix('/produtos')->name('products.')->group(function () {
        Route::middleware('can:products.index')->get('/', [ProductController::class, 'index'])->name('index');
        Route::middleware('can:products.store')->post('/', [ProductController::class, 'store'])->name('store');
        Route::middleware('can:products.update')->put('/{product}', [ProductController::class, 'update'])->name('update');
        Route::middleware('can:products.destroy')->delete('/{product}', [ProductController::class, 'destroy'])->name('destroy');
    });

    // Customers routes
    Route::prefix('/clientes')->name('customers.')->group(function () {
        Route::middleware('can:customers.index')->get('/', [CustomerController::class, 'index'])->name('index');
        Route::middleware('can:customers.store')->post('/', [CustomerController::class, 'store'])->name('store');
        Route::middleware('can:customers.update')->put('/{customer}', [CustomerController::class, 'update'])->name('update');
        Route::middleware('can:customers.destroy')->delete('/{customer}', [CustomerController::class, 'destroy'])->name('destroy');
    });

    // Orders routes
    Route::prefix('/pedidos')->name('orders.')->group(function () {
        Route::middleware('can:orders.index')->get('/', [OrderController::class, 'index'])->name('index');
        Route::middleware('can:orders.index')->get('/hoje', [OrderController::class, 'todayIndex'])->name('today');
        Route::middleware('can:orders.store')->get('/criar', [OrderController::class, 'create'])->name('create');
        Route::middleware('can:orders.store')->post('/', [OrderController::class, 'store'])->name('store');

        Route::prefix('/{order}')->group(function () {
            Route::middleware('can:orders.index')->get('/', [OrderController::class, 'show'])->name('show');
            Route::middleware('can:orders.update')->get('/editar', [OrderController::class, 'edit'])->name('edit');
            Route::middleware('can:orders.update')->put('/', [OrderController::class, 'update'])->name('update');
            Route::middleware('can:orders.update')->patch('/concluir', [OrderController::class, 'conclude'])->name('conclude');
            Route::middleware('can:orders.update')->patch('/cancelar', [OrderController::class, 'cancel'])->name('cancel');
            Route::middleware('can:orders.update')->patch('/reabrir', [OrderController::class, 'reopen'])->name('reopen');

        });
    });
});

require __DIR__.'/settings.php';
