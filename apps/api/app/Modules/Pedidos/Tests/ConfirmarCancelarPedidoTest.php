<?php

namespace App\Modules\Pedidos\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaNegocios;
use Tests\TestCase;

/**
 * Flujos críticos "Checkout" (confirmación) y "Sincronización de inventario"
 * — ver docs/development/testing.md. Confirmar/cancelar vive en el panel
 * (Sanctum + resolve-tenant), así que aquí sí se prueba multi-tenant real,
 * a diferencia de CheckoutStockTest sobre el endpoint público.
 */
class ConfirmarCancelarPedidoTest extends TestCase
{
    use RefreshDatabase;
    use CreaNegocios;

    public function test_confirmar_descuenta_stock_y_cambia_el_estado(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['stock' => 10]);
        $pedido = $this->crearPedido($tenant, $producto, cantidad: 3);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedido->id}/confirmar")
            ->assertOk()
            ->assertJsonPath('data.estado', 'confirmado');

        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'stock' => 7]);
    }

    public function test_confirmar_dos_veces_el_mismo_pedido_se_rechaza(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['stock' => 10]);
        $pedido = $this->crearPedido($tenant, $producto, cantidad: 3);

        $this->actingAs($admin, 'sanctum')->postJson("/api/v1/pedidos/{$pedido->id}/confirmar")->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedido->id}/confirmar")
            ->assertStatus(409)
            ->assertJsonPath('error.codigo', 'ESTADO_INVALIDO');

        // Un solo descuento de stock, no dos.
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'stock' => 7]);
    }

    public function test_cancelar_pedido_pendiente_no_toca_el_stock(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['stock' => 10]);
        $pedido = $this->crearPedido($tenant, $producto, cantidad: 3);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedido->id}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.estado', 'cancelado');

        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'stock' => 10]);
    }

    public function test_cancelar_pedido_ya_confirmado_devuelve_el_stock(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['stock' => 10]);
        $pedido = $this->crearPedido($tenant, $producto, cantidad: 3);

        $this->actingAs($admin, 'sanctum')->postJson("/api/v1/pedidos/{$pedido->id}/confirmar")->assertOk();
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'stock' => 7]);

        $this->actingAs($admin, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedido->id}/cancelar")
            ->assertOk()
            ->assertJsonPath('data.estado', 'cancelado');

        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'stock' => 10]);
    }

    public function test_un_tenant_no_puede_confirmar_ni_cancelar_pedidos_de_otro_tenant(): void
    {
        $tenantA = $this->crearTenant('Negocio A');
        $tenantB = $this->crearTenant('Negocio B');
        $adminA = $this->crearUsuarioAdmin($tenantA, 'admin.a@prueba.test');

        $productoB = $this->crearProducto($tenantB, ['nombre' => 'Producto del negocio B', 'stock' => 10]);
        $pedidoB = $this->crearPedido($tenantB, $productoB, cantidad: 4);

        $this->actingAs($adminA, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedidoB->id}/confirmar")
            ->assertStatus(404);

        $this->actingAs($adminA, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedidoB->id}/cancelar")
            ->assertStatus(404);

        // El pedido y el stock de B quedan intactos: A nunca pudo tocarlos.
        $this->assertDatabaseHas('pedidos', ['id' => $pedidoB->id, 'estado' => 'pendiente_pago']);
        $this->assertDatabaseHas('productos', ['id' => $productoB->id, 'stock' => 10]);
    }

    public function test_confirmar_un_pedido_de_un_tenant_no_afecta_el_stock_de_otro_tenant(): void
    {
        // Caso explícito del flujo crítico "sincronización de inventario":
        // dos tenants con un producto que se llama igual y el mismo stock —
        // confirmar el pedido de uno no debe tocar el stock del otro.
        $tenantA = $this->crearTenant('Negocio A');
        $tenantB = $this->crearTenant('Negocio B');
        $adminA = $this->crearUsuarioAdmin($tenantA, 'admin.a@prueba.test');

        $productoA = $this->crearProducto($tenantA, ['nombre' => 'Sérum Igual', 'stock' => 10]);
        $productoB = $this->crearProducto($tenantB, ['nombre' => 'Sérum Igual', 'stock' => 10]);
        $pedidoA = $this->crearPedido($tenantA, $productoA, cantidad: 6);

        $this->actingAs($adminA, 'sanctum')
            ->postJson("/api/v1/pedidos/{$pedidoA->id}/confirmar")
            ->assertOk();

        $this->assertDatabaseHas('productos', ['id' => $productoA->id, 'stock' => 4]);
        $this->assertDatabaseHas('productos', ['id' => $productoB->id, 'stock' => 10]);
    }
}
