<?php

namespace App\Modules\Catalogo\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Borrado suave — mismo criterio que Producto, ver ese modelo. */
#[Fillable(['nombre', 'slug', 'descripcion'])]
class Rutina extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    public function productos(): BelongsToMany
    {
        return $this->belongsToMany(Producto::class, 'rutina_producto')
            ->withPivot('orden')
            ->orderByPivot('orden');
    }
}
