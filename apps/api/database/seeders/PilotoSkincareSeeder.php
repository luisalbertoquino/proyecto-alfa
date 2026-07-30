<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalogo\Models\Categoria;
use App\Modules\Catalogo\Models\Producto;
use App\Shared\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Siembra el negocio piloto real de Proyecto Alfa: la tienda de skincare
 * (ver docs/estado-actual.md). Nombre de tienda y catálogo real siguen
 * pendientes de definir con el negocio; estos son datos de ejemplo para
 * poder probar el flujo completo mientras tanto.
 */
class PilotoSkincareSeeder extends Seeder
{
    public function run(): void
    {
        // El nombre real del negocio todavía no está definido con Angie — se
        // usa "Skincare Piloto" como marcador presentable (nada de anotaciones
        // tipo "(por definir)" aquí: este valor se muestra tal cual en la UI
        // del panel y de la tienda, ver docs/estado-actual.md para el estado
        // real de esta decisión pendiente).
        $tenant = Tenant::firstOrCreate(
            ['slug' => 'skincare-piloto'],
            ['nombre' => 'Skincare Piloto'],
        );

        User::firstOrCreate(
            ['email' => 'admin@skincarepiloto.test'],
            [
                'tenant_id' => $tenant->id,
                'name' => 'Admin Piloto',
                'password' => Hash::make('password'),
            ],
        );

        $limpieza = Categoria::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'limpieza-facial'],
            ['nombre' => 'Limpieza facial'],
        );

        $hidratacion = Categoria::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'hidratacion'],
            ['nombre' => 'Hidratación'],
        );

        $proteccion = Categoria::firstOrCreate(
            ['tenant_id' => $tenant->id, 'slug' => 'proteccion-solar'],
            ['nombre' => 'Protección solar'],
        );

        $productos = [
            ['categoria_id' => $limpieza->id, 'nombre' => 'Gel limpiador facial suave', 'slug' => 'gel-limpiador-facial-suave', 'precio' => 45000, 'stock' => 30],
            ['categoria_id' => $limpieza->id, 'nombre' => 'Agua micelar', 'slug' => 'agua-micelar', 'precio' => 38000, 'stock' => 25],
            ['categoria_id' => $hidratacion->id, 'nombre' => 'Serum de ácido hialurónico', 'slug' => 'serum-acido-hialuronico', 'precio' => 65000, 'stock' => 20],
            ['categoria_id' => $hidratacion->id, 'nombre' => 'Crema hidratante en gel', 'slug' => 'crema-hidratante-en-gel', 'precio' => 52000, 'stock' => 18],
            ['categoria_id' => $proteccion->id, 'nombre' => 'Protector solar FPS 50', 'slug' => 'protector-solar-fps-50', 'precio' => 58000, 'stock' => 22],
        ];

        foreach ($productos as $producto) {
            Producto::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $producto['slug']],
                [
                    'categoria_id' => $producto['categoria_id'],
                    'nombre' => $producto['nombre'],
                    'precio' => $producto['precio'],
                    'stock' => $producto['stock'],
                    'activo' => true,
                ],
            );
        }
    }
}
