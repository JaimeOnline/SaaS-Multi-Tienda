<?php

use App\Http\Controllers\CartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PublicCatalogController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderManagementController;
use App\Http\Controllers\StoreSettingsController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\DashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/tienda/{slug}', [PublicCatalogController::class, 'show'])
    ->name('store.public');

Route::post('/carrito/productos/{product}', [CartController::class, 'add'])
    ->name('cart.add');

Route::get('/carrito', [CartController::class, 'index'])
    ->name('cart.index');

Route::post('/pedidos', [OrderController::class, 'store'])
    ->name('orders.store');

Route::patch('/carrito/productos/{product}', [CartController::class, 'update'])
    ->name('cart.update');

Route::delete('/carrito/productos/{product}', [CartController::class, 'remove'])
    ->name('cart.remove');

Route::get('/dashboard', [DashboardController::class, '__invoke'])
    ->middleware(['auth', 'verified', 'active.subscription'])
    ->name('dashboard');

Route::middleware(['auth', 'active.subscription'])->group(function () {
    Route::get('/store/settings', [StoreSettingsController::class, 'edit'])
        ->name('store.settings.edit');

    Route::post('/subscriptions/entrepreneur-trial', [SubscriptionController::class, 'startEntrepreneurTrial'])
        ->name('subscriptions.entrepreneur-trial.start');

    Route::post('/subscriptions/reactivate', [SubscriptionController::class, 'reactivateSubscription'])
        ->name('subscriptions.reactivate');

    Route::patch('/store/settings', [StoreSettingsController::class, 'update'])
        ->name('store.settings.update');

    Route::get('/categories', [CategoryController::class, 'index'])
        ->name('categories.index');

    Route::post('/categories', [CategoryController::class, 'store'])
        ->name('categories.store');

    Route::patch('/categories/{category}', [CategoryController::class, 'update'])
        ->name('categories.update');

    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])
        ->name('categories.destroy');

    Route::delete('/categories', [CategoryController::class, 'bulkDestroy'])
        ->name('categories.bulk-delete');

    Route::get('/orders', [OrderManagementController::class, 'index'])
        ->name('orders.index');

    Route::patch('/orders/{order}', [OrderManagementController::class, 'update'])
        ->name('orders.update');

    Route::delete('/orders', [OrderManagementController::class, 'bulkDestroy'])
        ->name('orders.bulk-delete');

    Route::patch('/orders/bulk-toggle-status', [OrderManagementController::class, 'bulkToggleStatus'])
        ->name('orders.bulk-toggle-status');

    Route::get('/products', [ProductController::class, 'index'])
        ->name('products.index');

    Route::post('/products', [ProductController::class, 'store'])
        ->name('products.store');

    Route::patch('/products/{product}', [ProductController::class, 'update'])
        ->name('products.update');

    Route::delete('/products/{product}', [ProductController::class, 'destroy'])
        ->name('products.destroy');

    Route::delete('/products', [ProductController::class, 'bulkDestroy'])
        ->name('products.bulk-delete');

    Route::patch('/products/bulk-toggle-availability', [ProductController::class, 'bulkToggleAvailability'])
        ->name('products.bulk-toggle-availability');

    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__ . '/auth.php';
