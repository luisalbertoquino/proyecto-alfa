<?php

namespace App\Shared\Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Concerns\CreaNegocios;
use Tests\TestCase;

/**
 * Flujo crítico "Autenticación y aislamiento multi-tenant" —
 * ver docs/development/testing.md. La aserción que de verdad importa no es
 * "el tenant A ve sus datos", es "el tenant A NO ve los datos del tenant B".
 */
class AutenticacionYAislamientoTenantTest extends TestCase
{
    use RefreshDatabase;
    use CreaNegocios;

    public function test_login_exitoso_devuelve_token_y_tenant(): void
    {
        $tenant = $this->crearTenant('Skincare de Prueba');
        $usuario = $this->crearUsuarioAdmin($tenant, 'admin@prueba.test');

        $respuesta = $this->postJson('/api/v1/login', [
            'email' => 'admin@prueba.test',
            'password' => 'password',
        ]);

        $respuesta->assertOk()
            ->assertJsonPath('data.usuario.tenant.id', $tenant->id)
            ->assertJsonPath('data.usuario.tenant.nombre', 'Skincare de Prueba')
            ->assertJsonStructure(['data' => ['token', 'usuario' => ['id', 'nombre', 'email', 'tenant']]]);
    }

    public function test_login_con_credenciales_invalidas_se_rechaza(): void
    {
        $tenant = $this->crearTenant();
        $this->crearUsuarioAdmin($tenant, 'admin@prueba.test');

        $respuesta = $this->postJson('/api/v1/login', [
            'email' => 'admin@prueba.test',
            'password' => 'contraseña-incorrecta',
        ]);

        $respuesta->assertStatus(422)
            ->assertJsonPath('error.codigo', 'VALIDACION');
    }

    public function test_endpoint_protegido_sin_token_se_rechaza(): void
    {
        $this->getJson('/api/v1/me')
            ->assertStatus(401)
            ->assertJsonPath('error.codigo', 'NO_AUTENTICADO');
    }

    public function test_un_tenant_no_puede_ver_productos_de_otro_tenant(): void
    {
        $tenantA = $this->crearTenant('Negocio A');
        $tenantB = $this->crearTenant('Negocio B');
        $adminA = $this->crearUsuarioAdmin($tenantA, 'admin.a@prueba.test');

        $productoA = $this->crearProducto($tenantA, ['nombre' => 'Producto de A']);
        $productoB = $this->crearProducto($tenantB, ['nombre' => 'Producto de B']);

        // El listado del panel de A solo debe traer su propio producto.
        $this->actingAs($adminA, 'sanctum')
            ->getJson('/api/v1/productos')
            ->assertOk()
            ->assertJsonPath('data.0.nombre', 'Producto de A')
            ->assertJsonMissing(['nombre' => 'Producto de B']);

        // Aserción negativa explícita: A no puede leer el producto de B por id,
        // aunque conozca el id exacto.
        $this->actingAs($adminA, 'sanctum')
            ->getJson("/api/v1/productos/{$productoB->id}")
            ->assertStatus(404)
            ->assertJsonPath('error.codigo', 'NO_ENCONTRADO');

        // Ni borrarlo.
        $this->actingAs($adminA, 'sanctum')
            ->deleteJson("/api/v1/productos/{$productoB->id}")
            ->assertStatus(404);

        $this->assertDatabaseHas('productos', ['id' => $productoB->id]);
        $this->assertDatabaseHas('productos', ['id' => $productoA->id]);
    }
}
