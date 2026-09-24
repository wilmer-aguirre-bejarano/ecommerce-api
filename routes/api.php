<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\OrderController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\ProductController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;



    // ============================================
    // RUTAS PÚBLICAS (cualquiera puede acceder)
    // ============================================
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);

    // Productos publicos: ver catalogo
    Route::get('/products', [ProductController::class, 'index']);
    Route::get('/products/{product}', [ProductController::class, 'show']);

    //Categorias publicas
    Route::get('/categories',[CategoryController::class,'index']);
    Route::get('/categories/{category}',[CategoryController::class,'show']);


    // ============================================
    // RUTAS PROTEGIDAS (requieren token)
    // ============================================

    Route::middleware('auth:sanctum')->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::apiResource('orders', OrderController::class);

        // Órdenes (solo usuarios logueados)
        Route::get('/orders', [OrderController::class, 'index']);
        Route::post('/orders', [OrderController::class, 'store']);
        Route::get('/orders/{order}', [OrderController::class, 'show']);


        Route::get('/me', [AuthController::class, 'me']);
        // 🚨 IMPORTANTE ENTENDERLO 🚨
        // Estas rutas van DENTRO del grupo 'auth:sanctum' porque
        // solo un usuario logueado puede crear/editar/eliminar productos.
        // Cuando añadamos el middleware 'admin', solo el rol admin podrá.
        Route::middleware('admin')->group(function(){
            Route::post('/products', [ProductController::class, 'store']);
            Route::put('/products/{product}', [ProductController::class, 'update']);
            Route::delete('/products/{product}', [ProductController::class, 'destroy']);

           // Route::apiResource('categories', CategoryController::class);
        });

    });

    //???????
    // Route::middleware('admin')->group(function () {
    //     Route::get('/me', [AuthController::class, 'me']);
    //     Route::apiResource('products', ProductController::class)->except(['index', 'show']);
    //     Route::apiResource('categories', CategoryController::class);
    // });
