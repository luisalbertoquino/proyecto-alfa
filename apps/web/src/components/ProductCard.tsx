"use client";

import { useState } from "react";
import Link from "next/link";
import type { Producto } from "@/types/tienda";
import { formatearPrecio } from "@/lib/formato";
import { useCarrito } from "@/context/CarritoContext";

export function ProductCard({ producto }: { producto: Producto }) {
  const { agregar } = useCarrito();
  const [agregado, setAgregado] = useState(false);

  function agregarRapido() {
    agregar(producto, 1);
    setAgregado(true);
    setTimeout(() => setAgregado(false), 1500);
  }

  return (
    <div className="overflow-hidden rounded-lg border border-neutral-200 transition hover:border-neutral-400">
      <Link href={`/productos/${producto.slug}`} className="block">
        <div className="aspect-square bg-neutral-100">
          {producto.imagen_url ? (
            // eslint-disable-next-line @next/next/no-img-element -- imagen externa (Unsplash), sin necesidad del optimizador de Next para un prototipo
            <img
              src={producto.imagen_url}
              alt={producto.nombre}
              className="h-full w-full object-cover"
              loading="lazy"
            />
          ) : (
            <div className="flex h-full w-full items-center justify-center text-sm text-neutral-400">
              Sin imagen
            </div>
          )}
        </div>
        <div className="p-4 pb-0">
          {producto.categoria && (
            <p className="text-xs uppercase tracking-wide text-neutral-500">
              {producto.categoria.nombre}
            </p>
          )}
          <h2 className="mt-1 font-medium text-neutral-900">{producto.nombre}</h2>
          <p className="mt-2 text-lg font-semibold">
            {formatearPrecio(producto.precio)}
          </p>
        </div>
      </Link>

      <div className="p-4 pt-3">
        {producto.stock === 0 ? (
          <p className="text-sm text-red-600">Agotado</p>
        ) : (
          <button
            onClick={agregarRapido}
            className="w-full rounded-full bg-brand px-4 py-2 text-sm text-white transition hover:bg-brand/90"
          >
            {agregado ? "Agregado ✓" : "Agregar al carrito"}
          </button>
        )}
      </div>
    </div>
  );
}
