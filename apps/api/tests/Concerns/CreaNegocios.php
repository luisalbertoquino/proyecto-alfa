<?php

namespace Tests\Concerns;

use App\Modules\Catalogo\Models\Producto;
use App\Modules\Pedidos\Models\Cliente;
use App\Modules\Pedidos\Models\DetallePedido;
use App\Modules\Pedidos\Models\Pedido;
use App\Models\User;
use App\Shared\Models\Tenant;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Helpers para crear datos de prueba multi-tenant sin pasar por factories
 * nuevas para cada modelo — ver docs/development/testing.md: "ningún
 * seeder, factory o fixture de test genera datos de un solo tenant por
 * defecto". Cada test que usa esto crea explícitamente los tenants que
 * necesita, nunca uno solo por accidente.
 */
trait CreaNegocios
{
    protected function crearTenant(?string $nombre = null, ?string $dominioApi = null): Tenant
    {
        return Tenant::factory()->create([
            'nombre' => $nombre ?? fake()->unique()->company(),
            'dominio_api' => $dominioApi,
        ]);
    }

    protected function crearUsuarioAdmin(Tenant $tenant, ?string $email = null): User
    {
        return User::create([
            'tenant_id' => $tenant->id,
            'name' => 'Admin de prueba',
            'email' => $email ?? Str::random(10).'@prueba.test',
            'password' => Hash::make('password'),
        ]);
    }

    /**
     * @param  array<string, mixed>  $atributos
     */
    protected function crearProducto(Tenant $tenant, array $atributos = []): Producto
    {
        app()->instance('currentTenantId', $tenant->id);

        $nombre = $atributos['nombre'] ?? 'Producto de prueba '.Str::random(6);

        return Producto::create(array_merge([
            'nombre' => $nombre,
            'slug' => Str::slug($nombre).'-'.Str::random(4),
            'precio' => 50000,
            'stock' => 10,
            'activo' => true,
        ], $atributos));
    }

    protected function crearCliente(Tenant $tenant): Cliente
    {
        app()->instance('currentTenantId', $tenant->id);

        return Cliente::create([
            'nombre' => 'Cliente de prueba',
            'email' => Str::random(10).'@prueba.test',
        ]);
    }

    /**
     * Pedido con un solo detalle, sobre el producto dado.
     */
    protected function crearPedido(Tenant $tenant, Producto $producto, int $cantidad = 1, string $estado = 'pendiente_pago'): Pedido
    {
        app()->instance('currentTenantId', $tenant->id);

        $cliente = $this->crearCliente($tenant);

        $pedido = Pedido::create([
            'cliente_id' => $cliente->id,
            'estado' => $estado,
            'total' => $producto->precio * $cantidad,
        ]);

        DetallePedido::create([
            'pedido_id' => $pedido->id,
            'producto_id' => $producto->id,
            'cantidad' => $cantidad,
            'precio_unitario' => $producto->precio,
        ]);

        return $pedido->fresh(['detalles']);
    }
}
