<?php

namespace App\Modules\Catalogo\Services;

use App\Modules\Catalogo\Models\Rutina;
use Illuminate\Support\Str;

class RutinaService
{
    /**
     * @param  array<string, mixed>  $datos
     * @param  int[]  $productosOrdenados
     */
    public function crear(array $datos, array $productosOrdenados): Rutina
    {
        $datos['slug'] = $this->slugUnico($datos['nombre']);
        $rutina = Rutina::create($datos);
        $this->sincronizarProductos($rutina, $productosOrdenados);

        return $rutina;
    }

    /**
     * @param  array<string, mixed>  $datos
     * @param  int[]  $productosOrdenados
     */
    public function actualizar(Rutina $rutina, array $datos, array $productosOrdenados): Rutina
    {
        if ($datos['nombre'] !== $rutina->nombre) {
            $datos['slug'] = $this->slugUnico($datos['nombre'], ignorarId: $rutina->id);
        }

        $rutina->update($datos);
        $this->sincronizarProductos($rutina, $productosOrdenados);

        return $rutina;
    }

    /**
     * @param  int[]  $productosOrdenados
     */
    private function sincronizarProductos(Rutina $rutina, array $productosOrdenados): void
    {
        $conOrden = [];
        foreach (array_values($productosOrdenados) as $indice => $productoId) {
            $conOrden[$productoId] = ['orden' => $indice];
        }
        $rutina->productos()->sync($conOrden);
    }

    private function slugUnico(string $nombre, ?int $ignorarId = null): string
    {
        $base = Str::slug($nombre);
        $slug = $base;
        $contador = 2;

        while (
            Rutina::where('slug', $slug)
                ->when($ignorarId, fn ($q) => $q->whereKeyNot($ignorarId))
                ->exists()
        ) {
            $slug = "{$base}-{$contador}";
            $contador++;
        }

        return $slug;
    }
}
