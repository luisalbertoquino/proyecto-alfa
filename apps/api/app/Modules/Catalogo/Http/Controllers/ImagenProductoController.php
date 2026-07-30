<?php

namespace App\Modules\Catalogo\Http\Controllers;

use App\Modules\Catalogo\Models\ImagenProducto;
use App\Modules\Catalogo\Models\Producto;
use App\Modules\Catalogo\Services\GaleriaProductoService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Galería opcional de fotos adicionales por producto (además de la foto de
 * portada, `imagen_url`, que se maneja en ProductoController). Panel
 * administrativo — requiere login.
 */
class ImagenProductoController
{
    public function __construct(
        private readonly GaleriaProductoService $galeria,
    ) {}

    public function store(Request $request, int $productoId): JsonResponse
    {
        $producto = Producto::findOrFail($productoId);

        $request->validate([
            'imagenes' => ['required', 'array', 'min:1', 'max:8'],
            'imagenes.*' => ['image', 'max:4096'],
        ]);

        $this->galeria->agregar($producto, $request->file('imagenes'));

        return response()->json(['data' => $producto->fresh()->imagenes], 201);
    }

    public function destroy(int $productoId, int $imagenId): JsonResponse
    {
        // findOrFail ya queda scopeado por tenant (TenantScope); además se
        // confirma que la imagen pertenece al producto de la URL, para que
        // nadie borre una imagen de otro producto por id adivinado.
        $imagenProducto = ImagenProducto::where('producto_id', $productoId)->findOrFail($imagenId);

        $this->galeria->eliminar($imagenProducto);

        return response()->json(['data' => ['mensaje' => 'Imagen eliminada.']]);
    }
}
