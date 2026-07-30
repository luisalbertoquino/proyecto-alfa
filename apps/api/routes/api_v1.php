<?php

use App\Modules\Catalogo\Http\Controllers\CategoriaController;
use App\Modules\Catalogo\Http\Controllers\ProductoController;
use App\Modules\Catalogo\Http\Controllers\TiendaProductoController;
use App\Modules\Pedidos\Http\Controllers\PedidoController;
use App\Modules\Pedidos\Http\Controllers\TiendaPedidoController;
use App\Shared\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Ver docs/architecture/apis.md: rutas en español, plural, sin verbos
// (las acciones de cambio de estado de pedido — confirmar/cancelar — son
// la excepción de siempre en REST para transiciones que no son CRUD).

Route::post('/login', [AuthController::class, 'login']);

// Tienda pública (apps/web): sin login, tenant resuelto por
// ResolvePublicTenant — ver ese middleware para la limitación actual
// (un solo tenant fijo, hasta que exista resolución real por dominio).
Route::middleware(['resolve-public-tenant'])->prefix('tienda')->group(function () {
    Route::get('/productos', [TiendaProductoController::class, 'index']);
    Route::get('/productos/{slug}', [TiendaProductoController::class, 'show']);
    Route::post('/pedidos', [TiendaPedidoController::class, 'store']);
});

// Panel administrativo (apps/admin): requiere login.
Route::middleware(['auth:sanctum', 'resolve-tenant'])->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

    Route::get('/productos', [ProductoController::class, 'index']);
    Route::post('/productos', [ProductoController::class, 'store']);
    Route::get('/productos/{id}', [ProductoController::class, 'show'])->whereNumber('id');
    Route::patch('/productos/{id}', [ProductoController::class, 'update'])->whereNumber('id');
    Route::delete('/productos/{id}', [ProductoController::class, 'destroy'])->whereNumber('id');

    Route::get('/categorias', [CategoriaController::class, 'index']);
    Route::post('/categorias', [CategoriaController::class, 'store']);

    Route::get('/pedidos', [PedidoController::class, 'index']);
    Route::get('/pedidos/{id}', [PedidoController::class, 'show'])->whereNumber('id');
    Route::post('/pedidos/{id}/confirmar', [PedidoController::class, 'confirmar'])->whereNumber('id');
    Route::post('/pedidos/{id}/cancelar', [PedidoController::class, 'cancelar'])->whereNumber('id');
});
