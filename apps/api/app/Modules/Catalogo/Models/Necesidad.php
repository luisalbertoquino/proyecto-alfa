<?php

namespace App\Modules\Catalogo\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['nombre', 'slug'])]
class Necesidad extends Model
{
    use BelongsToTenant;

    // "Necesidad" pluraliza mal en inglés ("necesidads") — Eloquent no sabe
    // que en español es "necesidades", hay que fijar el nombre de tabla.
    protected $table = 'necesidades';

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'producto_necesidad');
    }
}
