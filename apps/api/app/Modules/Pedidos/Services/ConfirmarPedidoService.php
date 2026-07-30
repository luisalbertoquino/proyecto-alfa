<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Models\Producto;
use App\Modules\Pedidos\Exceptions\EstadoPedidoInvalidoException;
use App\Modules\Pedidos\Exceptions\StockInsuficienteException;
use App\Modules\Pedidos\Models\Pedido;
use Illuminate\Support\Facades\DB;

/**
 * Confirmar un pedido (Semana 3): el emprendedor ya verificó el pago a mano
 * (checkout con pago simulado, Semana 2) y aquí es donde el stock se
 * descuenta de verdad — nunca antes, para no reservar stock de un pedido
 * que termina sin pagarse (ver docs/business/roadmap.md).
 *
 * Vuelve a validar stock en este momento (no confía en el stock que había
 * al crear el pedido, que puede haber cambiado) y usa lockForUpdate para
 * que dos confirmaciones simultáneas no descuenten stock que ya no existe.
 */
class ConfirmarPedidoService
{
    public function ejecutar(Pedido $pedido): Pedido
    {
        if ($pedido->estado !== 'pendiente_pago') {
            throw new EstadoPedidoInvalidoException(
                $pedido->id,
                $pedido->estado,
                "El pedido #{$pedido->id} no está pendiente de pago (estado actual: {$pedido->estado}).",
            );
        }

        return DB::transaction(function () use ($pedido) {
            foreach ($pedido->detalles as $detalle) {
                $producto = Producto::whereKey($detalle->producto_id)->lockForUpdate()->first();

                if (! $producto || $producto->stock < $detalle->cantidad) {
                    throw new StockInsuficienteException(
                        $detalle->producto_id,
                        $producto?->nombre ?? "producto #{$detalle->producto_id}",
                        $producto?->stock ?? 0,
                    );
                }

                $producto->decrement('stock', $detalle->cantidad);
            }

            $pedido->update(['estado' => 'confirmado']);

            return $pedido->fresh(['detalles.producto', 'cliente']);
        });
    }
}
