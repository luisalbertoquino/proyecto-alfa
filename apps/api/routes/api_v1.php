<?php

use App\Modules\Catalogo\Http\Controllers\ProductoController;
use App\Modules\Catalogo\Http\Controllers\TiendaProductoController;
use App\Modules\Pedidos\Http\Controllers\TiendaPedidoController;
use App\Shared\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Ver docs/architecture/apis.md: rutas en español, plural, sin verbos.

Route::post('/login', [AuthController::class, 'login']);

// Tienda pública (apps/web): sin login, tenant resuelto por
// ResolvePublicTenant — ver ese middleware para la limitación actual
// (un solo tenant fijo, hasta que exista resolución real por dominio).
Route::middleware(['resolve-public-tenant'])->prefix('tienda')->group(function () {
    Route::get('/productos', [TiendaProductoController::class, 'index']);
    Route::get('/productos/{slug}', [TiendaProductoController::class, 'show']);
    Route::post('/pedidos', [TiendaPedidoController::class, 'store']);
});

// Panel administrativo (apps/admin, Semana 3): requiere login.
Route::middleware(['auth:sanctum', 'resolve-tenant'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/productos', [ProductoController::class, 'index']);
});
