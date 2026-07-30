<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Models\Categoria;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoriaController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Categoria::orderBy('nombre')->get(),
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $datos = $request->validate([
            'nombre' => ['required', 'string', 'max:255'],
        ]);

        $categoria = Categoria::create([
            'nombre' => $datos['nombre'],
            'slug' => Str::slug($datos['nombre']),
        ]);

        return response()->json(['data' => $categoria], 201);
    }
}
