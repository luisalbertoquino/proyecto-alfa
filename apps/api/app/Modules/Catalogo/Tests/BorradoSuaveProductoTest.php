<?php

namespace App\Modules\Catalogo\Tests;

use App\Modules\Catalogo\Models\Producto;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaNegocios;
use Tests\TestCase;

/**
 * Un producto "eliminado" desde el panel nunca debe desaparecer de la base
 * de datos de verdad: puede estar referenciado por un pedido real
 * (`detalle_pedidos.producto_id` es `restrictOnDelete()`), y no hay garantía
 * legal de que un producto vendido pueda borrarse sin más — ver
 * docs/estado-actual.md. Este test cubre exactamente el problema real que
 * se encontró en el droplet: borrar un producto con un pedido asociado
 * tronaba con 500 por la restricción de la base de datos.
 */
class BorradoSuaveProductoTest extends TestCase
{
    use RefreshDatabase;
    use CreaNegocios;

    public function test_eliminar_un_producto_lo_oculta_pero_no_borra_la_fila(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['nombre' => 'Sérum de prueba']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/productos/{$producto->id}")
            ->assertOk();

        // Oculto de cualquier consulta normal (panel y tienda usan las mismas).
        $this->assertNull(Producto::find($producto->id));

        // Pero la fila sigue existiendo de verdad, con deleted_at marcado.
        $this->assertDatabaseHas('productos', ['id' => $producto->id]);
        $this->assertSoftDeleted('productos', ['id' => $producto->id]);
    }

    public function test_eliminar_un_producto_con_un_pedido_asociado_no_rompe_el_historial_del_pedido(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['nombre' => 'Producto ya vendido', 'stock' => 5]);
        $pedido = $this->crearPedido($tenant, $producto, cantidad: 2);

        // Antes de este fix, esto respondía 500 (restrictOnDelete de verdad
        // impedía el borrado físico en cuanto había un pedido de por medio).
        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/productos/{$producto->id}")
            ->assertOk();

        // El detalle del pedido sigue resolviendo su producto sin error.
        $detalle = $pedido->detalles()->first();
        $this->assertNotNull($detalle->producto()->withTrashed()->first());
        $this->assertSame('Producto ya vendido', $detalle->producto()->withTrashed()->first()->nombre);
    }

    public function test_un_producto_eliminado_no_aparece_en_la_tienda_ni_en_el_panel(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $producto = $this->crearProducto($tenant, ['nombre' => 'Producto a ocultar']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/productos/{$producto->id}")
            ->assertOk();

        $this->actingAs($admin, 'sanctum')
            ->getJson('/api/v1/productos')
            ->assertOk()
            ->assertJsonMissing(['nombre' => 'Producto a ocultar']);

        $this->actingAs($admin, 'sanctum')
            ->getJson("/api/v1/productos/{$producto->id}")
            ->assertStatus(404);
    }

    public function test_crear_un_producto_nuevo_con_el_mismo_nombre_de_uno_eliminado_no_choca_de_slug(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);
        $original = $this->crearProducto($tenant, ['nombre' => 'Crema Reemplazable']);

        $this->actingAs($admin, 'sanctum')
            ->deleteJson("/api/v1/productos/{$original->id}")
            ->assertOk();

        $respuesta = $this->actingAs($admin, 'sanctum')->postJson('/api/v1/productos', [
            'nombre' => 'Crema Reemplazable',
            'precio' => 50000,
            'stock' => 10,
        ]);

        $respuesta->assertCreated();
        $this->assertNotSame($original->slug, $respuesta->json('data.slug'));
    }
}
