<?php

namespace App\Modules\Pedidos\Models;

use App\Shared\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

// Estados posibles (ver docs/business/reglas-de-negocio.md): pendiente_pago,
// pendiente_stock, confirmado, despachado, entregado, cancelado.
#[Fillable(['cliente_id', 'estado', 'canal_origen', 'total'])]
class Pedido extends Model
{
    use BelongsToTenant;

    protected function casts(): array
    {
        return [
            'total' => 'decimal:2',
        ];
    }

    public function cliente(): BelongsTo
    {
        return $this->belongsTo(Cliente::class);
    }

    public function detalles(): HasMany
    {
        return $this->hasMany(DetallePedido::class);
    }
}
