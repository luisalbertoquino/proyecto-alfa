import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { ProductCard } from "@/components/ProductCard";
import type { Necesidad, Producto } from "@/types/tienda";

// Catálogo real (stock, precios) — se renderiza en cada visita, nunca se
// congela en el build. Evita además que el build dependa de tener la API
// alcanzable en el momento de compilar (ver docs/estado-actual.md, despliegue).
export const dynamic = "force-dynamic";

export default async function Home({
  searchParams,
}: {
  searchParams: Promise<{ necesidad?: string }>;
}) {
  const { necesidad } = await searchParams;

  const [productos, necesidades] = await Promise.all([
    apiFetch<Producto[]>(
      necesidad ? `/tienda/productos?necesidad=${necesidad}` : "/tienda/productos",
    ),
    apiFetch<Necesidad[]>("/tienda/necesidades"),
  ]);

  const necesidadActiva = necesidades.find((n) => n.slug === necesidad);
  const destacados = productos.filter((p) => p.destacado);

  return (
    <div>
      <h1 className="text-2xl font-semibold">
        {necesidadActiva ? `Para: ${necesidadActiva.nombre}` : "Nuestros productos"}
      </h1>
      <p className="mt-1 text-neutral-600">
        {necesidadActiva
          ? `Productos recomendados para ${necesidadActiva.nombre.toLowerCase()}.`
          : "Cuidado de la piel, seleccionado con cariño."}
      </p>

      {necesidadActiva ? (
        <Link href="/" className="mt-2 inline-block text-sm underline">
          Quitar filtro
        </Link>
      ) : (
        necesidades.length > 0 && (
          <div className="mt-4 flex flex-wrap gap-2">
            {necesidades.map((n) => (
              <Link
                key={n.id}
                href={`/?necesidad=${n.slug}`}
                className="rounded-full bg-neutral-100 px-3 py-1 text-sm text-neutral-700 hover:bg-neutral-200"
              >
                {n.nombre}
              </Link>
            ))}
          </div>
        )
      )}

      {!necesidadActiva && destacados.length > 0 && (
        <section className="mt-10">
          <h2 className="text-lg font-semibold">Más vendidos</h2>
          <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            {destacados.map((producto) => (
              <ProductCard key={producto.id} producto={producto} />
            ))}
          </div>
        </section>
      )}

      <section className="mt-10">
        {!necesidadActiva && destacados.length > 0 && (
          <h2 className="text-lg font-semibold">Todo el catálogo</h2>
        )}

        {productos.length === 0 ? (
          <p className="mt-8 text-neutral-500">
            Todavía no hay productos publicados.
          </p>
        ) : (
          <div className="mt-4 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
            {productos.map((producto) => (
              <ProductCard key={producto.id} producto={producto} />
            ))}
          </div>
        )}
      </section>
    </div>
  );
}
