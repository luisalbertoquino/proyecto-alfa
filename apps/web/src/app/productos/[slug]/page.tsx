import { notFound } from "next/navigation";
import { apiFetch, ApiRequestError } from "@/lib/api";
import { formatearPrecio } from "@/lib/formato";
import { AgregarAlCarrito } from "@/components/AgregarAlCarrito";
import { GaleriaProducto } from "@/components/GaleriaProducto";
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
      <GaleriaProducto
        nombre={producto.nombre}
        imagenPortada={producto.imagen_url}
        imagenesGaleria={(producto.imagenes ?? []).map((i) => i.url)}
      />
      {producto.categoria && (
        <p className="mt-4 text-xs uppercase tracking-wide text-neutral-500">
          {producto.categoria.nombre}
        </p>
      )}
      <h1 className="mt-1 text-2xl font-semibold">{producto.nombre}</h1>
      <p className="mt-3 text-xl font-semibold">
        {formatearPrecio(producto.precio)}
      </p>
      {producto.necesidades && producto.necesidades.length > 0 && (
        <div className="mt-3 flex flex-wrap gap-2">
          {producto.necesidades.map((n) => (
            <span
              key={n.id}
              className="rounded-full bg-neutral-100 px-3 py-1 text-xs text-neutral-600"
            >
              {n.nombre}
            </span>
          ))}
        </div>
      )}
      {producto.descripcion && (
        <p className="mt-4 text-neutral-700">{producto.descripcion}</p>
      )}
      <AgregarAlCarrito producto={producto} />
    </article>
  );
}
