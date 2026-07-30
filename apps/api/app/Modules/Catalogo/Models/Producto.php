<?php

namespace App\Modules\Catalogo\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['categoria_id', 'nombre', 'slug', 'descripcion', 'sku', 'imagen_url', 'precio', 'stock', 'activo', 'destacado'])]
class Producto extends Model
{
    use BelongsToTenant;

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
