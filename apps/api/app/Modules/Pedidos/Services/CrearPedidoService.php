<?php

namespace App\Modules\Pedidos\Services;

use App\Modules\Catalogo\Models\Producto;
use App\Modules\Pedidos\Exceptions\StockInsuficienteException;
use App\Modules\Pedidos\Models\Cliente;
use App\Modules\Pedidos\Models\Pedido;
use Illuminate\Support\Facades\DB;

/**
 * Checkout de la tienda pública (Semana 2 del sprint): crea el pedido en
 * estado "pendiente_pago" — el admin confirma el pago a mano (ver
 * docs/business/roadmap.md, Fase 2). NO descuenta stock todavía: eso pasa
 * al confirmar el pedido (Semana 3), no al crearlo, para no reservar stock
 * de un pedido que nunca se paga.
 *
 * El precio se toma siempre del Producto en este momento, nunca de lo que
 * mande el cliente — evita que alguien manipule el precio desde el body.
 */
class CrearPedidoService
{
    /**
     * @param  array{nombre: string, email: string, telefono: ?string}  $datosCliente
     * @param  array<int, array{producto_id: int, cantidad: int}>  $items
     */
    public function ejecutar(array $datosCliente, array $items): Pedido
    {
        return DB::transaction(function () use ($datosCliente, $items) {
            $cliente = Cliente::firstOrCreate(
                ['tenant_id' => app('currentTenantId'), 'email' => $datosCliente['email']],
                ['nombre' => $datosCliente['nombre'], 'telefono' => $datosCliente['telefono'] ?? null],
            );

            $pedido = Pedido::create([
                'cliente_id' => $cliente->id,
                'estado' => 'pendiente_pago',
                'canal_origen' => 'tienda_propia',
                'total' => 0,
            ]);

            $total = 0;

            foreach ($items as $item) {
                $producto = Producto::where('activo', true)->findOrFail($item['producto_id']);

                if ($producto->stock < $item['cantidad']) {
                    throw new StockInsuficienteException($producto->id, $producto->nombre, $producto->stock);
                }

                $pedido->detalles()->create([
                    'producto_id' => $producto->id,
                    'cantidad' => $item['cantidad'],
                    'precio_unitario' => $producto->precio,
                ]);

                $total += $producto->precio * $item['cantidad'];
            }

            $pedido->update(['total' => $total]);

            return $pedido->load('detalles.producto', 'cliente');
        });
    }
}
