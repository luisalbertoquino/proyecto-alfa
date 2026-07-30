<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Models\Producto;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Catálogo visible para la tienda pública (sin login). Solo expone
 * productos activos — a diferencia de ProductoController (panel
 * administrativo), que ve todo el catálogo del tenant.
 */
class TiendaProductoController
{
    public function index(Request $request): JsonResponse
    {
        $productos = Producto::with('categoria', 'necesidades')
            ->where('activo', true)
            ->when($request->query('destacado'), fn ($q) => $q->where('destacado', true))
            ->when($request->query('necesidad'), function ($q, $slugNecesidad) {
                $q->whereHas('necesidades', fn ($nq) => $nq->where('slug', $slugNecesidad));
            })
            ->orderBy('nombre')
            ->get();

        return response()->json(['data' => $productos]);
    }

    public function show(string $slug): JsonResponse
    {
        $producto = Producto::with('categoria', 'necesidades', 'imagenes')
            ->where('activo', true)
            ->where('slug', $slug)
            ->firstOrFail();

        return response()->json(['data' => $producto]);
    }
}
