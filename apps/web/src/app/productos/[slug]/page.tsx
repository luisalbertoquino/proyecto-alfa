import { notFound } from "next/navigation";
import { apiFetch, ApiRequestError } from "@/lib/api";
import { formatearPrecio } from "@/lib/formato";
import { AgregarAlCarrito } from "@/components/AgregarAlCarrito";
import type { Producto } from "@/types/tienda";

export default async function ProductoPage({
  params,
}: {
  params: Promise<{ slug: string }>;
}) {
  const { slug } = await params;

  let producto: Producto;
  try {
    producto = await apiFetch<Producto>(`/tienda/productos/${slug}`);
  } catch (error) {
    if (error instanceof ApiRequestError && error.codigo === "NO_ENCONTRADO") {
      notFound();
    }
    throw error;
  }

  return (
    <article className="max-w-xl">
      {producto.imagen_url && (
        <div className="aspect-square w-full overflow-hidden rounded-lg bg-neutral-100">
          {/* eslint-disable-next-line @next/next/no-img-element -- imagen externa (Unsplash), sin necesidad del optimizador de Next para un prototipo */}
          <img
            src={producto.imagen_url}
            alt={producto.nombre}
            className="h-full w-full object-cover"
          />
        </div>
      )}
      {producto.categoria && (
        <p className="mt-4 text-xs uppercase tracking-wide text-neutral-500">
          {producto.categoria.nombre}
        </p>
      )}
      <h1 className="mt-1 text-2xl font-semibold">{producto.nombre}</h1>
      <p className="mt-3 text-xl font-semibold">
        {formatearPrecio(producto.precio)}
      </p>
      {producto.descripcion && (
        <p className="mt-4 text-neutral-700">{producto.descripcion}</p>
      )}
      <AgregarAlCarrito producto={producto} />
    </article>
  );
}
