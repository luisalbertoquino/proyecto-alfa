import { apiFetch } from "@/lib/api";
import { ProductCard } from "@/components/ProductCard";
import type { Producto } from "@/types/tienda";

export default async function Home() {
  const productos = await apiFetch<Producto[]>("/tienda/productos");

  return (
    <div>
      <h1 className="text-2xl font-semibold">Nuestros productos</h1>
      <p className="mt-1 text-neutral-600">
        Cuidado de la piel, seleccionado con cariño.
      </p>

      {productos.length === 0 ? (
        <p className="mt-8 text-neutral-500">
          Todavía no hay productos publicados.
        </p>
      ) : (
        <div className="mt-8 grid grid-cols-1 gap-4 sm:grid-cols-2 md:grid-cols-3">
          {productos.map((producto) => (
            <ProductCard key={producto.id} producto={producto} />
          ))}
        </div>
      )}
    </div>
  );
}
