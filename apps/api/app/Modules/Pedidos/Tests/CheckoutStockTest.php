<?php

namespace App\Modules\Pedidos\Tests;

use App\Shared\Models\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaNegocios;
use Tests\TestCase;

/**
 * Flujo crítico "Checkout / creación de pedido" — ver docs/development/testing.md.
 *
 * Este archivo no prueba aislamiento multi-tenant (usa un solo tenant, el
 * que resuelve por defecto vía ResolvePublicTenant) porque no es lo que
 * cubre — el aislamiento del panel administrativo vive en
 * ConfirmarCancelarPedidoTest, y el aislamiento de la tienda pública por
 * dominio (sin ningún login de por medio) vive en
 * App\Shared\Tests\ResolucionPublicaPorDominioTest.
 */
class CheckoutStockTest extends TestCase
{
    use RefreshDatabase;
    use CreaNegocios;

    private function crearTenantPublico(): Tenant
    {
        // El slug debe coincidir con TENANT_PUBLICO_SLUG (por defecto "skincare-piloto").
        return Tenant::factory()->create(['slug' => config('tenant.slug_publico_por_defecto')]);
    }

    public function test_checkout_con_stock_suficiente_crea_el_pedido_pendiente_de_pago(): void
    {
        $tenant = $this->crearTenantPublico();
        $producto = $this->crearProducto($tenant, ['precio' => 45000, 'stock' => 10]);

        $respuesta = $this->postJson('/api/v1/tienda/pedidos', [
            'cliente' => ['nombre' => 'Cliente de Prueba', 'email' => 'cliente@prueba.test'],
            'items' => [['producto_id' => $producto->id, 'cantidad' => 3]],
        ]);

        $respuesta->assertCreated()
            ->assertJsonPath('data.estado', 'pendiente_pago')
            ->assertJsonPath('data.total', '135000.00');

        // El checkout NO descuenta stock todavía — eso pasa al confirmar
        // (ver docs/business/roadmap.md, Semana 2/3).
        $this->assertDatabaseHas('productos', ['id' => $producto->id, 'stock' => 10]);
    }

    public function test_checkout_sin_stock_suficiente_se_rechaza(): void
    {
        $tenant = $this->crearTenantPublico();
        $producto = $this->crearProducto($tenant, ['stock' => 2]);

        $respuesta = $this->postJson('/api/v1/tienda/pedidos', [
            'cliente' => ['nombre' => 'Cliente de Prueba', 'email' => 'cliente@prueba.test'],
            'items' => [['producto_id' => $producto->id, 'cantidad' => 5]],
        ]);

        $respuesta->assertStatus(422)
            ->assertJsonPath('error.codigo', 'STOCK_INSUFICIENTE');

        $this->assertDatabaseCount('pedidos', 0);
    }
}
