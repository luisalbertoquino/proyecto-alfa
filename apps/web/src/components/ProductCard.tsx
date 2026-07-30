import Link from "next/link";
import type { Producto } from "@/types/tienda";
import { formatearPrecio } from "@/lib/formato";

export function ProductCard({ producto }: { producto: Producto }) {
  return (
    <Link
      href={`/productos/${producto.slug}`}
      className="block rounded-lg border border-neutral-200 p-4 transition hover:border-neutral-400"
    >
      {producto.categoria && (
        <p className="text-xs uppercase tracking-wide text-neutral-500">
          {producto.categoria.nombre}
        </p>
      )}
      <h2 className="mt-1 font-medium text-neutral-900">{producto.nombre}</h2>
      <p className="mt-2 text-lg font-semibold">
        {formatearPrecio(producto.precio)}
      </p>
      {producto.stock === 0 && (
        <p className="mt-1 text-sm text-red-600">Agotado</p>
      )}
    </Link>
  );
}
