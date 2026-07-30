<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Models\Producto;
use App\Modules\Pedidos\Exceptions\EstadoPedidoInvalidoException;
use App\Modules\Pedidos\Models\Pedido;
use Illuminate\Support\Facades\DB;

/**
 * Ver docs/business/reglas-de-negocio.md: un pedido se cancela sin costo
 * mientras no haya sido despachado. Si ya estaba "confirmado" (el stock ya
 * se descontó), la cancelación devuelve ese stock; si estaba
 * "pendiente_pago", el stock nunca se tocó y no hay nada que devolver.
 */
class CancelarPedidoService
{
    private const ESTADOS_CANCELABLES = ['pendiente_pago', 'pendiente_stock', 'confirmado'];

    public function ejecutar(Pedido $pedido): Pedido
    {
        if (! in_array($pedido->estado, self::ESTADOS_CANCELABLES, true)) {
            throw new EstadoPedidoInvalidoException(
                $pedido->id,
                $pedido->estado,
                "El pedido #{$pedido->id} ya no se puede cancelar (estado actual: {$pedido->estado}).",
            );
        }

        return DB::transaction(function () use ($pedido) {
            if ($pedido->estado === 'confirmado') {
                foreach ($pedido->detalles as $detalle) {
                    Producto::whereKey($detalle->producto_id)->lockForUpdate()->increment('stock', $detalle->cantidad);
                }
            }

            $pedido->update(['estado' => 'cancelado']);

            return $pedido->fresh(['detalles.producto', 'cliente']);
        });
    }
}
