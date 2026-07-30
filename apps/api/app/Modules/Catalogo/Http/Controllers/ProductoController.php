<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Http\Requests\GuardarProductoRequest;
use App\Modules\Catalogo\Models\Producto;
use App\Modules\Catalogo\Services\ProductoService;
use Illuminate\Http\JsonResponse;

/**
 * Panel administrativo (requiere login) — ver docs/business/roadmap.md
 * Semana 3. El catálogo público (sin login) vive en TiendaProductoController.
 */
class ProductoController
{
    public function __construct(
        private readonly ProductoService $productos,
    ) {}

    public function index(): JsonResponse
    {
        $productos = Producto::with('categoria')
            ->orderBy('nombre')
            ->paginate(15);

        return response()->json([
            'data' => $productos->items(),
            'meta' => [
                'version' => 'v1',
                'pagina_actual' => $productos->currentPage(),
                'total' => $productos->total(),
                'por_pagina' => $productos->perPage(),
            ],
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json(['data' => Producto::with('categoria')->findOrFail($id)]);
    }

    public function store(GuardarProductoRequest $request): JsonResponse
    {
        $producto = $this->productos->crear(
            $request->safe()->except('imagen'),
            $request->slug(),
            $request->file('imagen'),
        );

        return response()->json(['data' => $producto->load('categoria')], 201);
    }

    public function update(GuardarProductoRequest $request, int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);
        $producto = $this->productos->actualizar(
            $producto,
            $request->safe()->except('imagen'),
            $request->slug(),
            $request->file('imagen'),
        );

        return response()->json(['data' => $producto->load('categoria')]);
    }

    public function destroy(int $id): JsonResponse
    {
        $this->productos->eliminar(Producto::findOrFail($id));

        return response()->json(['data' => ['mensaje' => 'Producto eliminado.']]);
    }
}
