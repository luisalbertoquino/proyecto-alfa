<?php

namespace App\Modules\Pedidos\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

// El comprador final en la tienda de un tenant (ver diccionario-del-negocio.md).
// Sin login propio en este prototipo: se captura al hacer un pedido (checkout
// como invitado), no requiere cuenta — ver docs/business/roadmap.md Semana 2.
#[Fillable(['nombre', 'email', 'telefono'])]
class Cliente extends Model
{
    use BelongsToTenant;

    public function pedidos(): HasMany
    {
        return $this->hasMany(Pedido::class);
    }
}
