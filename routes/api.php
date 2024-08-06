<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

use App\Http\Controllers\AuthController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\ProductDetailController;
use App\Http\Controllers\CartController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\OrderController;

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/auth/me', [AuthController::class, 'getMe']);
    Route::get('/auth/logout', [AuthController::class, 'logout']);
    Route::patch('/auth/update', [AuthController::class, 'updateProfile']);
    Route::patch('/auth/change-password', [AuthController::class, 'changePassword']);

    Route::middleware('role:user')->group(function() {
        // cart
        Route::get('/carts', [CartController::class, 'getCurrentCarts']);
        Route::post('/carts/products', [CartController::class, 'addProductCart']);
        Route::patch('/carts/products', [CartController::class,'updateProductsCart']);
        Route::delete('/carts/products/{id}', [CartController::class,'deleteProductCart']);
    
        Route::get('/users/orders', [OrderController::class, 'getUserOrders']);
        Route::get('/users/orders/{id}', [OrderController::class, 'getUserOrder']);
        Route::post('/users/orders', [OrderController::class, 'createUserOrder']);
    });

    Route::middleware('role:admin|super-admin')->group(function() {
        // user
        Route::get('/users', [UserController::class, 'getAllUsers']);
        Route::get('/users/{id}', [UserController::class, 'getUser']);
        Route::post('/users', [UserController::class, 'createUser']);
        Route::patch('/users/{id}', [UserController::class, 'updateUser']);
        Route::delete('/users/{id}', [UserController::class, 'deleteUser']);

        //category
        Route::post('/categories', [CategoryController::class, 'createCategory']);
        Route::patch('/categories/{id}', [CategoryController::class, 'updateCategory']);
        Route::delete('/categories/{id}', [CategoryController::class, 'deleteCategory']);

        //product
        Route::post('/products', [ProductController::class, 'createProduct']);
        Route::patch('/products/{id}', [ProductController::class, 'updateProduct']);
        Route::delete('/products/{id}', [ProductController::class, 'deleteProduct']);

        //product-detail
        Route::post('/products/{id}/detail', [ProductDetailController::class, 'createProductDetail']);
        Route::patch('/products/{id}/detail', [ProductDetailController::class, 'updateProductDetail']);
        Route::delete('/products/{id}/detail', [ProductDetailController::class, 'deleteProductDetail']);

        //order
        Route::get('/orders', [OrderController::class, 'getAllOrders']);
        Route::patch('/orders/{id}', [OrderController::class, 'updateOrderStatus']);
    });
});

Route::get('/categories', [CategoryController::class, 'getAllCategories']);
Route::get('/categories/{id}', [CategoryController::class, 'getCategory']);
Route::get('/categories/{id}/products', [CategoryController::class, 'getProductsCategory']);

Route::get('/products', [ProductController::class, 'getAllProducts']);
Route::get('/products/{id}', [ProductController::class, 'getProduct']);

Route::get('/products/{id}/details', [ProductDetailController::class, 'getProductDetail']);