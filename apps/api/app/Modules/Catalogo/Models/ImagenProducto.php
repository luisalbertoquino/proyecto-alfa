<?php

namespace App\Modules\Catalogo\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['producto_id', 'url', 'orden'])]
class ImagenProducto extends Model
{
    use BelongsToTenant;

    // Eloquent infiere "imagen_productos" del nombre de la clase; la tabla
    // real se llama producto_imagenes (ver la migración) — hay que fijarlo.
    protected $table = 'producto_imagenes';

    public function producto(): BelongsTo
    {
        return $this->belongsTo(Producto::class);
    }
}
