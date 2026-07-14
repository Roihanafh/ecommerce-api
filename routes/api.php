<?php

use App\Http\Controllers\Api\Auth\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CheckoutController;
use App\Http\Controllers\Api\MidtransWebhookController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {

    Route::prefix('auth')->group(function () {

        Route::post('/register', [AuthController::class, 'register']);

        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:api')->group(function () {

            Route::get('/me', [AuthController::class, 'me']);

            Route::post('/logout', [AuthController::class, 'logout']);

            Route::post('/refresh', [AuthController::class, 'refresh']);

        });

    });

    Route::middleware('auth:api')->group(function () {

        Route::apiResource('categories', CategoryController::class);

        // products: update pakai POST karena PHP tidak parse multipart/form-data pada PUT
        Route::apiResource('products', ProductController::class)->except(['update']);
        Route::post('products/{product}', [ProductController::class, 'update']);

        Route::get('/cart', [CartController::class, 'index']);
        Route::post('/cart', [CartController::class, 'store']);
        Route::put('/cart/{cartItem}', [CartController::class, 'update']);
        Route::delete('/cart/{cartItem}', [CartController::class, 'destroy']);
        Route::delete('/cart', [CartController::class, 'clear']);

        Route::post('/checkout', [CheckoutController::class, 'checkout']);
        Route::get('/orders', [CheckoutController::class, 'history']);
        Route::get('/orders/{id}', [CheckoutController::class, 'show']);

        Route::post('/payments/{order}', [CheckoutController::class, 'pay']);
    });

    // Midtrans webhook — tidak butuh auth JWT, diverifikasi via signature_key
    Route::post('/webhooks/midtrans', [MidtransWebhookController::class, 'notify']);

});
