<?php

namespace App\Shared\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaNegocios;
use Tests\TestCase;

/**
 * Primera prueba real de aislamiento multi-tenant en la RUTA PÚBLICA (sin
 * login) — hasta ahora solo existía en el panel administrativo (ver
 * AutenticacionYAislamientoTenantTest). Confirma que
 * App\Shared\Http\Middleware\ResolvePublicTenant resuelve el tenant
 * correcto por el Host de la petición, sin ningún token de autenticación
 * de por medio.
 */
class ResolucionPublicaPorDominioTest extends TestCase
{
    use RefreshDatabase;
    use CreaNegocios;

    public function test_la_tienda_publica_resuelve_el_tenant_por_el_host_y_aisla_el_catalogo(): void
    {
        $tenantA = $this->crearTenant(dominioApi: 'tenant-a-api.prueba.test');
        $tenantB = $this->crearTenant(dominioApi: 'tenant-b-api.prueba.test');

        $this->crearProducto($tenantA, ['nombre' => 'Serum de A']);
        $this->crearProducto($tenantB, ['nombre' => 'Serum de B']);

        // El cliente de pruebas de Laravel reconstruye el Host desde la URL
        // completa que se le pasa (Symfony Request::create() lo sobreescribe
        // sin importar qué header se mande aparte) — por eso hay que usar
        // una URL absoluta con el host que se quiere probar, no un header.
        $respuestaA = $this->getJson('http://tenant-a-api.prueba.test/api/v1/tienda/productos');
        $respuestaA->assertOk()
            ->assertJsonFragment(['nombre' => 'Serum de A'])
            ->assertJsonMissing(['nombre' => 'Serum de B']);

        $respuestaB = $this->getJson('http://tenant-b-api.prueba.test/api/v1/tienda/productos');
        $respuestaB->assertOk()
            ->assertJsonFragment(['nombre' => 'Serum de B'])
            ->assertJsonMissing(['nombre' => 'Serum de A']);
    }

    public function test_un_host_no_registrado_cae_al_tenant_por_defecto_de_configuracion(): void
    {
        $piloto = $this->crearTenant(nombre: 'Piloto');
        $piloto->update(['slug' => config('tenant.slug_publico_por_defecto')]);
        $this->crearProducto($piloto, ['nombre' => 'Producto del piloto']);

        $respuesta = $this->getJson('http://host-nunca-registrado.example.com/api/v1/tienda/productos');

        $respuesta->assertOk()->assertJsonFragment(['nombre' => 'Producto del piloto']);
    }
}
