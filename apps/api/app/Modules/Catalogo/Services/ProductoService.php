<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Models\Producto;
use Illuminate\Support\Str;

class ProductoService
{
    /**
     * @param  array<string, mixed>  $datos
     */
    public function crear(array $datos, string $nombreParaSlug): Producto
    {
        $datos['slug'] = $this->slugUnico($nombreParaSlug);

        return Producto::create($datos);
    }

    /**
     * @param  array<string, mixed>  $datos
     */
    public function actualizar(Producto $producto, array $datos, string $nombreParaSlug): Producto
    {
        if ($nombreParaSlug !== $producto->nombre) {
            $datos['slug'] = $this->slugUnico($nombreParaSlug, ignorarId: $producto->id);
        }

        $producto->update($datos);

        return $producto;
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
