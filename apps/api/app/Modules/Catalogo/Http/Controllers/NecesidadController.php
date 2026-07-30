<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Models\Necesidad;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Panel administrativo. La lista pública para el filtro de la tienda vive
 * en TiendaProductoController (no requiere login).
 */
class NecesidadController
{
    public function index(): JsonResponse
    {
        return response()->json(['data' => Necesidad::orderBy('nombre')->get()]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $necesidad = Necesidad::create([
            'nombre' => $datos['nombre'],
            'slug' => Str::slug($datos['nombre']),
        ]);

        return response()->json(['data' => $necesidad], 201);
    }
}
