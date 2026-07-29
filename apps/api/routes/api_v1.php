<?php

use App\Modules\Catalogo\Http\Controllers\ProductoController;
use App\Shared\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Ver docs/architecture/apis.md: rutas en español, plural, sin verbos.

Route::post('/login', [AuthController::class, 'login']);

Route::middleware(['auth:sanctum', 'resolve-tenant'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/productos', [ProductoController::class, 'index']);
});
