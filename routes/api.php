<?php

use App\Http\Controllers\ReviewController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
//use App\Http\Controllers\ProductController;
use App\Http\Controllers\api\ProductController;
use App\Http\Controllers\api\CartController;
//use App\Http\Controllers\CartController as ControllersCartController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\OtentikasiController;
use App\Http\Controllers\ForgotPasswordController;
use App\Http\Controllers\KasirController;
use App\Http\Controllers\ResetPasswordController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\OrderController;
use App\Http\Controllers\OrderItemController;


// use Illuminate\Foundation\Auth\EmailVerificationRequest;

// PRODUCT
Route::get('products', [ProductController::class, 'index']);
Route::get('products/{id}', [ProductController::class, 'show']);
Route::get('/product/search', [ProductController::class, 'search']);


// USER
Route::post('forgot-password', [ForgotPasswordController::class, 'sendResetLink']);
Route::post('resetpassword', [ResetPasswordController::class, 'resetPassword']);
Route::post('/register', [OtentikasiController::class, 'register']);
Route::get('/categories', [CategoryController::class, 'index']);
Route::get('/categories/{name}/products', [ProductController::class, 'getProductsByCategory']);
Route::post('/login', [OtentikasiController::class, 'login']);




Route::middleware('auth:sanctum')->group(function () {
    Route::post('/cart', [CartController::class, 'addToCart']);
    Route::get('/cart', [CartController::class, 'getCart']);
    Route::patch('/cart/{id}', [CartController::class, 'updateQuantity']);
    Route::delete('/cart/{id}', [CartController::class, 'removeFromCart']);
    Route::post('/logout', [OtentikasiController::class, 'logout']);
    Route::post('/checkout', [CartController::class, 'checkout'])->name('checkout');
    Route::get('/profile', [ProfileController::class, 'show']);
    Route::get('/transactions', [TransactionController::class, 'index']);
    Route::post('reviews', [ReviewController::class, 'store']);
    Route::put('/profile', [ProfileController::class, 'update']);
    Route::get('/midtrans/notification', [CartController::class, 'handleMidtransNotification']);
});

Route::apiResource('order-items', OrderItemController::class);
