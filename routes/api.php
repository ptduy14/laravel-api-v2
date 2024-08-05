<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\CartController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'getMe']);
    Route::get('/auth/logout', [AuthController::class, 'logout']);
    Route::put('/auth/update', [AuthController::class, 'update']);
    Route::patch('/auth/change-password', [AuthController::class, 'changePassword']);

    Route::middleware('role:admin|super-admin')->group(function() {
        // user
        Route::get('/users', [UserController::class, 'getAllUsers']);
        Route::get('/users/{id}', [UserController::class, 'getUser']);
        Route::post('/users', [UserController::class, 'createUser']);


    });

    // cart
    Route::get('/carts', [CartController::class, 'getCurrentCarts']);
    Route::post('/carts/products', [CartController::class, 'addProductCart']);
    Route::patch('/carts/products', [CartController::class,'updateProductsCart']);
    Route::delete('/carts/products/{id}', [CartController::class,'deleteProductCart']);
});

Route::get('/categories', [CategoryController::class, 'getAll']);
Route::get('/categories/{id}', [CategoryController::class, 'getById']);
Route::get('/categories/{id}/products', [CategoryController::class, 'getProductsCategory']);

Route::get('/products', [ProductController::class, 'getAll']);
Route::get('/products/{id}', [ProductController::class, 'getById']);

Route::get('/products/{id}/details', [ProductDetailController::class, 'getProductDetail']);