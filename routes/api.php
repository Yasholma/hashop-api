<?php

use Illuminate\Support\Facades\Route;
use \App\Http\Controllers\AuthController;
use \App\Http\Controllers\ProductController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

Route::group(['prefix' => 'auth'], function () {
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::get('/user-profile', [AuthController::class, 'userProfile']);
});

// products
Route::get('/products', [ProductController::class, 'index']);
Route::get('/products/{product}', [ProductController::class, 'show'])
    ->missing(fn () => ProductController::productNotFound());
Route::post('/products', [ProductController::class, 'store']);
Route::patch('/products/{productId}', [ProductController::class, 'update'])
    ->missing(fn () => ProductController::productNotFound());
Route::delete('/product/{productId}', [ProductController::class, 'delete'])
    ->missing(fn () => ProductController::productNotFound());

// checkout
Route::post('checkout', [ProductController::class, 'checkout']);
