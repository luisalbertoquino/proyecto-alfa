<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Http\Requests\GuardarRutinaRequest;
use App\Modules\Catalogo\Models\Rutina;
use App\Modules\Catalogo\Services\RutinaService;
use Illuminate\Http\JsonResponse;

/**
 * Rutinas sugeridas. index/show se usan tanto desde el panel como desde la
 * tienda pública (montados en ambos grupos de rutas); store/update/destroy
 * solo están registrados en el grupo con login.
 */
class RutinaController
{
    public function __construct(
        private readonly RutinaService $rutinas,
    ) {}

    public function index(): JsonResponse
    {
        return response()->json([
            'data' => Rutina::with('productos.categoria')->orderBy('nombre')->get(),
        ]);
    }

    public function show(int $id): JsonResponse
    {
        return response()->json([
            'data' => Rutina::with('productos.categoria')->findOrFail($id),
        ]);
    }

    public function store(GuardarRutinaRequest $request): JsonResponse
    {
        $rutina = $this->rutinas->crear(
            $request->safe()->except('productos'),
            $request->productosOrdenados(),
        );

        return response()->json(['data' => $rutina->load('productos.categoria')], 201);
    }

    public function update(GuardarRutinaRequest $request, int $id): JsonResponse
    {
        $rutina = Rutina::findOrFail($id);
        $rutina = $this->rutinas->actualizar(
            $rutina,
            $request->safe()->except('productos'),
            $request->productosOrdenados(),
        );

        return response()->json(['data' => $rutina->load('productos.categoria')]);
    }

    public function destroy(int $id): JsonResponse
    {
        Rutina::findOrFail($id)->delete();

        return response()->json(['data' => ['mensaje' => 'Rutina eliminada.']]);
    }
}
