<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Models\Producto;
use Illuminate\Http\JsonResponse;

/**
 * Catálogo visible para la tienda pública (sin login). Solo expone
 * productos activos — a diferencia de ProductoController (panel
 * administrativo), que ve todo el catálogo del tenant.
 */
class TiendaProductoController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Producto::with('categoria')
                ->where('activo', true)
                ->orderBy('nombre')
                ->get(),
        ]);
    }

    public function show(string $slug): JsonResponse
    {
        $producto = Producto::with('categoria')
            ->where('activo', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $producto]);
    }
}
