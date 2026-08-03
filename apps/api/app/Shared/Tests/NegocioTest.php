<?php

namespace App\Shared\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaNegocios;
use Tests\TestCase;

/**
 * Contenido institucional y theming reducido del negocio (color de marca,
 * tipografía) — ver App\Shared\Http\Controllers\NegocioController. No
 * existía ningún test para este controlador todavía.
 */
class NegocioTest extends TestCase
{
    use RefreshDatabase;
    use CreaNegocios;

    public function test_un_administrador_puede_actualizar_el_color_y_la_tipografia_de_su_negocio(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);

        $respuesta = $this->actingAs($admin, 'sanctum')->patchJson('/api/v1/negocio', [
            'color_primario' => '#B45309',
            'tipografia' => 'serif',
        ]);

        $respuesta->assertOk()
            ->assertJsonPath('data.color_primario', '#B45309')
            ->assertJsonPath('data.tipografia', 'serif');

        $this->assertDatabaseHas('tenants', [
            'id' => $tenant->id,
            'color_primario' => '#B45309',
            'tipografia' => 'serif',
        ]);
    }

    public function test_el_color_primario_debe_ser_un_hexadecimal_valido(): void
    {
        $tenant = $this->crearTenant();
        $admin = $this->crearUsuarioAdmin($tenant);

        $this->actingAs($admin, 'sanctum')
            ->patchJson('/api/v1/negocio', ['color_primario' => 'no-es-un-color'])
            ->assertStatus(422)
            ->assertJsonPath('error.codigo', 'VALIDACION');
    }

    public function test_la_ruta_publica_de_negocio_resuelve_el_color_del_tenant_correcto_por_dominio(): void
    {
        $tenantA = $this->crearTenant(dominioApi: 'tenant-a-api.prueba.test');
        $tenantB = $this->crearTenant(dominioApi: 'tenant-b-api.prueba.test');

        $tenantA->update(['color_primario' => '#111111', 'nombre' => 'Negocio A']);
        $tenantB->update(['color_primario' => '#B45309', 'nombre' => 'Negocio B']);

        $respuestaA = $this->getJson('http://tenant-a-api.prueba.test/api/v1/tienda/negocio');
        $respuestaA->assertOk()
            ->assertJsonPath('data.nombre', 'Negocio A')
            ->assertJsonPath('data.color_primario', '#111111');

        $respuestaB = $this->getJson('http://tenant-b-api.prueba.test/api/v1/tienda/negocio');
        $respuestaB->assertOk()
            ->assertJsonPath('data.nombre', 'Negocio B')
            ->assertJsonPath('data.color_primario', '#B45309');
    }
}
