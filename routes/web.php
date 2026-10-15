<?php

use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\CheckoutController;
use App\Http\Controllers\ContactController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeployController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\NewsController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductLineController;
use App\Http\Controllers\PushSubscriptionController;
use Illuminate\Support\Facades\Route;

Route::get('/', HomeController::class)->name('home');

// Customer Authentication
Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisterController::class, 'create'])->name('register');
    Route::post('/register', [RegisterController::class, 'store'])->name('register.store');

    Route::get('/login', [LoginController::class, 'create'])->name('login');
    Route::post('/login', [LoginController::class, 'store'])->name('login.store');
});

Route::post('/logout', [LoginController::class, 'destroy'])->middleware('auth')->name('logout');

// Customer Dashboard & Order Tracking
Route::middleware('auth')->prefix('dashboard')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');
    Route::get('/orders', [DashboardController::class, 'orders'])->name('dashboard.orders');
    Route::get('/orders/{order:reference}', [DashboardController::class, 'showOrder'])->name('dashboard.orders.show');
    Route::post('/orders/{order:reference}/reorder', [DashboardController::class, 'reorder'])->name('dashboard.orders.reorder');
    Route::get('/profile', [DashboardController::class, 'profile'])->name('dashboard.profile');
    Route::patch('/profile', [DashboardController::class, 'updateProfile'])->name('dashboard.profile.update');
});

Route::get('/products/{productLine:slug}', [ProductLineController::class, 'show'])->name('product-lines.show');
Route::get('/products/{productLine:slug}/{product:slug}', [ProductController::class, 'show'])
    ->withoutScopedBindings()
    ->name('products.show');

Route::get('/cart', [CartController::class, 'index'])->name('cart.index');
Route::post('/cart', [CartController::class, 'store'])->name('cart.store');
Route::patch('/cart/{product}', [CartController::class, 'update'])->name('cart.update');
Route::delete('/cart/{product}', [CartController::class, 'destroy'])->name('cart.destroy');

Route::get('/checkout', [CheckoutController::class, 'create'])->name('checkout.create');
Route::post('/checkout', [CheckoutController::class, 'store'])->middleware('throttle:10,1')->name('checkout.store');

Route::get('/orders/{order:reference}', [OrderController::class, 'show'])->middleware('signed')->name('orders.show');

Route::get('/news', [NewsController::class, 'index'])->name('news.index');
Route::get('/news/{post:slug}', [NewsController::class, 'show'])->name('news.show');

Route::get('/contact', [ContactController::class, 'show'])->name('contact');
Route::post('/contact', [ContactController::class, 'store'])->middleware('throttle:5,1')->name('contact.store');
Route::redirect('/pages/contact-us', '/contact', 301);

Route::get('/pages/{page:slug}', [PageController::class, 'show'])->name('pages.show');

Route::view('/offline', 'offline')->name('offline');
Route::post('/push/subscribe', [PushSubscriptionController::class, 'store'])->middleware('throttle:10,1')->name('push.subscribe');
Route::post('/push/unsubscribe', [PushSubscriptionController::class, 'destroy'])->middleware('throttle:10,1')->name('push.unsubscribe');

Route::prefix('_deploy/{token}')
    ->middleware('throttle:20,1')
    ->name('deploy.')
    ->group(function () {
        Route::get('/', [DeployController::class, 'show'])->name('show');
        Route::post('/run', [DeployController::class, 'run'])->name('run');
        Route::post('/admin', [DeployController::class, 'createAdmin'])->name('admin');
    });
