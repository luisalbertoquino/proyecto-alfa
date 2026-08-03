<?php

namespace App\Modules\Catalogo\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Borrado suave: "eliminar" un producto desde el panel nunca quita la fila
 * de la base de datos (queda con `deleted_at`), porque un pedido real puede
 * seguir referenciándolo (`detalle_pedidos.producto_id` es `restrictOnDelete`)
 * y no hay garantía legal de que un producto vendido pueda desaparecer sin
 * más. Eloquent ya lo excluye de cualquier consulta normal (tienda y panel)
 * sin que haga falta tocar ese código.
 */
#[Fillable(['categoria_id', 'nombre', 'slug', 'descripcion', 'sku', 'imagen_url', 'precio', 'stock', 'activo', 'destacado'])]
class Producto extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'precio' => 'decimal:2',
            'stock' => 'integer',
            'activo' => 'boolean',
            'destacado' => 'boolean',
        ];
    }

    public function categoria(): BelongsTo
    {
        return $this->belongsTo(Categoria::class);
    }

    /** Fotos adicionales, además de la foto de portada (imagen_url). */
    public function imagenes(): HasMany
    {
        return $this->hasMany(ImagenProducto::class)->orderBy('orden');
    }

    public function necesidades(): BelongsToMany
    {
        return $this->belongsToMany(Necesidad::class, 'producto_necesidad');
    }
}
