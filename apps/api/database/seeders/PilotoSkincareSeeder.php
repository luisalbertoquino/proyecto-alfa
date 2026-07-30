<?php

namespace Database\Seeders;

use App\Models\User;
use App\Modules\Catalogo\Models\Categoria;
use App\Modules\Catalogo\Models\Necesidad;
use App\Modules\Catalogo\Models\Producto;
use App\Shared\Models\Tenant;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Siembra el negocio piloto real de Proyecto Alfa: la tienda de skincare
 * (ver docs/estado-actual.md). Nombre de tienda y catálogo real siguen
 * pendientes de definir con el negocio.
 *
 * Mientras tanto, este catálogo de ejemplo usa nombres de productos de
 * skincare coreano realmente virales (investigado, no inventado — ver
 * docs/estado-actual.md para las fuentes) y fotografía de stock libre de
 * derechos (Unsplash) elegida por categoría, NO fotos reales de esas
 * marcas — usar la foto real de un producto de otra marca sí sería un
 * problema de derechos de autor, aunque sea solo para un prototipo local.
 */
class PilotoSkincareSeeder extends Seeder
{
    public function run(): void
    {
        // El nombre real del negocio todavía no está definido con Angie — se
        // usa "Skincare Piloto" como marcador presentable (nada de anotaciones
        // tipo "(por definir)" aquí: este valor se muestra tal cual en la UI
        // del panel y de la tienda).
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

        $categorias = [
            'limpieza-facial' => 'Limpieza facial',
            'tonicos-y-esencias' => 'Tónicos y esencias',
            'sueros-y-tratamientos' => 'Sueros y tratamientos',
            'hidratacion' => 'Hidratación',
            'proteccion-solar' => 'Protección solar',
            'mascarillas-y-labios' => 'Mascarillas y labios',
        ];

        $idCategoria = [];
        foreach ($categorias as $slug => $nombre) {
            $idCategoria[$slug] = Categoria::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $slug],
                ['nombre' => $nombre],
            )->id;
        }

        // "Necesidad de piel" — investigado de competencia real (chokchok.co,
        // rosavainilla.co), ver docs/estado-actual.md.
        $necesidades = [
            'acne' => 'Acné',
            'antiedad' => 'Cuidado antiedad',
            'manchas' => 'Manchas',
            'poros' => 'Poros',
            'puntos-negros' => 'Puntos negros',
            'rojez' => 'Rojez',
            'textura' => 'Textura',
            'luminosidad' => 'Luminosidad',
        ];

        $idNecesidad = [];
        foreach ($necesidades as $slug => $nombre) {
            $idNecesidad[$slug] = Necesidad::firstOrCreate(
                ['tenant_id' => $tenant->id, 'slug' => $slug],
                ['nombre' => $nombre],
            )->id;
        }

        // Catálogo de ejemplo (reemplaza cualquier producto sembrado antes,
        // para no acumular versiones viejas de prueba).
        Producto::where('tenant_id', $tenant->id)->delete();

        // Producto::create() no acepta tenant_id (no es fillable, a
        // propósito — ver App\Shared\Models\Concerns\BelongsToTenant). Se
        // asigna solo mediante el tenant resuelto en el contenedor, igual
        // que en una petición real pasada por el middleware resolve-tenant.
        app()->instance('currentTenantId', $tenant->id);

        $img = fn (string $id) => "https://images.unsplash.com/photo-{$id}?w=800&q=80&fit=crop";

        $productos = [
            [
                'categoria' => 'limpieza-facial',
                'nombre' => 'Gel Limpiador Espumoso pH Balanceado',
                'descripcion' => 'Limpieza suave que respeta la barrera cutánea, apto para uso diario mañana y noche.',
                'precio' => 32000, 'stock' => 40,
                'imagen_url' => $img('1748639320154-6ba118bccc74'),
                'necesidades' => ['acne', 'poros'], 'destacado' => false,
            ],
            [
                'categoria' => 'limpieza-facial',
                'nombre' => 'Aceite Limpiador Desmaquillante',
                'descripcion' => 'Disuelve maquillaje y protector solar sin resecar. Primer paso del double cleansing.',
                'precio' => 42000, 'stock' => 28,
                'imagen_url' => $img('1732861612232-50cbe19c1ae5'),
                'necesidades' => ['poros'], 'destacado' => false,
            ],
            [
                'categoria' => 'tonicos-y-esencias',
                'nombre' => 'Tónico Calmante Centella Asiática 77%',
                'descripcion' => 'Calma el enrojecimiento y prepara la piel para el resto de la rutina.',
                'precio' => 48000, 'stock' => 35,
                'imagen_url' => $img('1576426863848-c21f53c60b19'),
                'necesidades' => ['rojez', 'textura'], 'destacado' => true,
            ],
            [
                'categoria' => 'tonicos-y-esencias',
                'nombre' => 'Esencia Hidratante Multi-Uso',
                'descripcion' => 'Textura ligera que se puede aplicar en capas para hidratación profunda.',
                'precio' => 55000, 'stock' => 20,
                'imagen_url' => $img('1665763630810-e6251bdd392d'),
                'necesidades' => ['textura'], 'destacado' => false,
            ],
            [
                'categoria' => 'sueros-y-tratamientos',
                'nombre' => 'Sérum de Baba de Caracol 96%',
                'descripcion' => 'El sérum viral por excelencia: ayuda a mejorar textura y marcas de acné.',
                'precio' => 68000, 'stock' => 30,
                'imagen_url' => $img('1679394270597-e90694d70350'),
                'necesidades' => ['acne', 'textura'], 'destacado' => true,
            ],
            [
                'categoria' => 'sueros-y-tratamientos',
                'nombre' => 'Sérum de Vitamina C Iluminador',
                'descripcion' => 'Antioxidante, unifica el tono y da luminosidad con uso constante.',
                'precio' => 72000, 'stock' => 15,
                'imagen_url' => $img('1613803745799-ba6c10aace85'),
                'necesidades' => ['manchas', 'luminosidad'], 'destacado' => false,
            ],
            [
                'categoria' => 'sueros-y-tratamientos',
                'nombre' => 'Sérum de Niacinamida 10% + Zinc',
                'descripcion' => 'Controla brillo y minimiza poros. Uno de los más buscados en redes.',
                'precio' => 59000, 'stock' => 0,
                'imagen_url' => $img('1627811015433-368c148f6c3c'),
                'necesidades' => ['acne', 'poros'], 'destacado' => false,
            ],
            [
                'categoria' => 'hidratacion',
                'nombre' => 'Crema Hidratante en Gel Ligera',
                'descripcion' => 'Hidratación sin sensación grasosa, ideal para piel mixta a grasa.',
                'precio' => 46000, 'stock' => 33,
                'imagen_url' => $img('1629732047847-50219e9c5aef'),
                'necesidades' => ['poros'], 'destacado' => false,
            ],
            [
                'categoria' => 'hidratacion',
                'nombre' => 'Crema de Noche Nutritiva con Péptidos',
                'descripcion' => 'Textura rica para reparar la piel mientras duermes.',
                'precio' => 78000, 'stock' => 12,
                'imagen_url' => $img('1629380108574-40c083555579'),
                'necesidades' => ['antiedad', 'textura'], 'destacado' => false,
            ],
            [
                'categoria' => 'proteccion-solar',
                'nombre' => 'Protector Solar FPS 50 Toque Seco',
                'descripcion' => 'Sin rastro blanco, no engrasa. El paso que nunca se salta.',
                'precio' => 54000, 'stock' => 40,
                'imagen_url' => $img('1594055103006-7871176f1a7e'),
                'necesidades' => ['manchas'], 'destacado' => true,
            ],
            [
                'categoria' => 'proteccion-solar',
                'nombre' => 'Stick Protector Solar FPS 50',
                'descripcion' => 'Reaplicación fácil sobre maquillaje, perfecto para llevar en la cartera.',
                'precio' => 39000, 'stock' => 25,
                'imagen_url' => $img('1594325624708-75a0a6cf806f'),
                'necesidades' => ['manchas'], 'destacado' => false,
            ],
            [
                'categoria' => 'mascarillas-y-labios',
                'nombre' => 'Mascarilla de Colágeno Nocturna',
                'descripcion' => 'Se aplica antes de dormir para un efecto plump al despertar.',
                'precio' => 15000, 'stock' => 50,
                'imagen_url' => $img('1670201203150-bf8771401590'),
                'necesidades' => ['antiedad', 'luminosidad'], 'destacado' => false,
            ],
            [
                'categoria' => 'mascarillas-y-labios',
                'nombre' => 'Bálsamo Labial Nocturno',
                'descripcion' => 'El infaltable "lip sleeping mask" que arrasa en redes sociales.',
                'precio' => 62000, 'stock' => 45,
                'imagen_url' => $img('1498706045548-6239e299361c'),
                'necesidades' => [], 'destacado' => true,
            ],
            [
                'categoria' => 'mascarillas-y-labios',
                'nombre' => 'Parches Antigranitos (32 unidades)',
                'descripcion' => 'Absorben la impureza y protegen la zona mientras cicatriza.',
                'precio' => 28000, 'stock' => 60,
                'imagen_url' => $img('1597093218446-518d2127291a'),
                'necesidades' => ['acne', 'puntos-negros'], 'destacado' => true,
            ],
        ];

        foreach ($productos as $producto) {
            $nuevo = Producto::create([
                'categoria_id' => $idCategoria[$producto['categoria']],
                'nombre' => $producto['nombre'],
                'slug' => \Illuminate\Support\Str::slug($producto['nombre']),
                'descripcion' => $producto['descripcion'],
                'imagen_url' => $producto['imagen_url'],
                'precio' => $producto['precio'],
                'stock' => $producto['stock'],
                'activo' => true,
                'destacado' => $producto['destacado'],
            ]);

            $idsNecesidades = collect($producto['necesidades'])->map(fn ($slug) => $idNecesidad[$slug]);
            $nuevo->necesidades()->sync($idsNecesidades);
        }
    }
}
