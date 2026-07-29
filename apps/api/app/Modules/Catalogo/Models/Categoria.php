<?php

namespace App\Modules\Catalogo\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'slug'])]
class Categoria extends Model
{
    use BelongsToTenant;

    public function productos(): HasMany
    {
        return $this->hasMany(Producto::class);
    }
}
