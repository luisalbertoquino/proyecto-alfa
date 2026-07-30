<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Models\ImagenProducto;
use App\Modules\Catalogo\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class GaleriaProductoService
{
    /**
     * @param  UploadedFile[]  $imagenes
     */
    public function agregar(Producto $producto, array $imagenes): void
    {
        $orden = (int) $producto->imagenes()->max('orden');

        foreach ($imagenes as $imagen) {
            $ruta = $imagen->store('productos', 'public');

            $producto->imagenes()->create([
                'url' => Storage::disk('public')->url($ruta),
                'orden' => ++$orden,
            ]);
        }
    }

    public function eliminar(ImagenProducto $imagenProducto): void
    {
        $prefijoPropio = Storage::disk('public')->url('');

        if (str_starts_with($imagenProducto->url, $prefijoPropio)) {
            $ruta = ltrim(substr($imagenProducto->url, strlen($prefijoPropio)), '/');
            Storage::disk('public')->delete($ruta);
        }

        $imagenProducto->delete();
    }
}
