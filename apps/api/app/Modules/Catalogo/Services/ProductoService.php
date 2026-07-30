<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Models\Producto;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ProductoService
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos, string $nombreParaSlug, ?UploadedFile $imagen = null): Producto
    {
        $datos['slug'] = $this->slugUnico($nombreParaSlug);

        if ($imagen) {
            $datos['imagen_url'] = $this->guardarImagen($imagen);
        }

        return Producto::create($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Producto $producto, array $datos, string $nombreParaSlug, ?UploadedFile $imagen = null): Producto
    {
        if ($nombreParaSlug !== $producto->nombre) {
            $datos['slug'] = $this->slugUnico($nombreParaSlug, ignorarId: $producto->id);
        }

        if ($imagen) {
            $this->eliminarImagenPropia($producto->imagen_url);
            $datos['imagen_url'] = $this->guardarImagen($imagen);
        }

        $producto->update($datos);

        return $producto;
    }

    public function eliminar(Producto $producto): void
    {
        $this->eliminarImagenPropia($producto->imagen_url);
        $producto->delete();
    }

    /**
     * Guarda el archivo en el disco público (storage/app/public/productos,
     * servido vía el symlink public/storage — ver `php artisan storage:link`)
     * y devuelve la URL absoluta lista para guardar en `imagen_url`.
     */
    private function guardarImagen(UploadedFile $imagen): string
    {
        $ruta = $imagen->store('productos', 'public');

        return Storage::disk('public')->url($ruta);
    }

    /**
     * Si la imagen anterior era un archivo subido por nosotros (no una URL
     * externa como las fotos de ejemplo de Unsplash), se borra del disco al
     * reemplazarla — evita acumular archivos huérfanos.
     */
    private function eliminarImagenPropia(?string $imagenUrlAnterior): void
    {
        if (! $imagenUrlAnterior) {
            return;
        }

        $prefijoPropio = Storage::disk('public')->url('');

        if (! str_starts_with($imagenUrlAnterior, $prefijoPropio)) {
            return;
        }

        $ruta = ltrim(substr($imagenUrlAnterior, strlen($prefijoPropio)), '/');
        Storage::disk('public')->delete($ruta);
    }

    /**
     * Genera un slug único dentro del tenant actual, agregando -2, -3...
     * si ya existe (ver docs/standards/database.md: unique(tenant_id, slug)).
     */
    private function slugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $contador = 2;

        while (
            Producto::where('slug', $slug)
                ->when($ignorarId, fn ($q) => $q->whereKeyNot($ignorarId))
                ->exists()
        ) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }
}
