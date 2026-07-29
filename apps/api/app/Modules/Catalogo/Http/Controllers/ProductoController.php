<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Models\Producto;
use Illuminate\Http\JsonResponse;

/**
 * Controlador mínimo para verificar el modelo núcleo end-to-end (Semana 1).
 * CRUD completo del panel administrativo llega en la Semana 3.
 */
class ProductoController
{
    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Producto::with('categoria')->orderBy('nombre')->get(),
        ]);
    }
}
