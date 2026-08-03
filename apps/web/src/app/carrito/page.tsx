"use client";

import Link from "next/link";
import { useCarrito } from "@/context/CarritoContext";
import { formatearPrecio } from "@/lib/formato";

export default function CarritoPage() {
  const { items, actualizarCantidad, quitar, total } = useCarrito();

  if (items.length === 0) {
    return (
      <div>
        <h1 className="text-2xl font-semibold">Tu carrito está vacío</h1>
        <Link href="/" className="mt-4 inline-block underline">
          Ver productos
        </Link>
      </div>
    );
  }

  return (
    <div>
      <h1 className="text-2xl font-semibold">Tu carrito</h1>

      <ul className="mt-6 divide-y divide-neutral-200">
        {items.map((item) => (
          <li key={item.productoId} className="flex items-center gap-4 py-4">
            <div className="flex-1">
              <p className="font-medium">{item.nombre}</p>
              <p className="text-sm text-neutral-600">
                {formatearPrecio(item.precio)} c/u
              </p>
            </div>
            <input
              type="number"
              min={1}
              max={item.stock}
              value={item.cantidad}
              onChange={(e) =>
                actualizarCantidad(item.productoId, Number(e.target.value) || 1)
              }
              className="w-16 rounded border border-neutral-300 px-2 py-1 text-center"
            />
            <p className="w-28 text-right font-medium">
              {formatearPrecio(Number(item.precio) * item.cantidad)}
            </p>
            <button
              onClick={() => quitar(item.productoId)}
              className="text-sm text-red-600 underline"
            >
              Quitar
            </button>
          </li>
        ))}
      </ul>

      <div className="mt-6 flex items-center justify-between border-t border-neutral-200 pt-4">
        <p className="text-lg font-semibold">Total</p>
        <p className="text-lg font-semibold">{formatearPrecio(total)}</p>
      </div>

      <Link
        href="/checkout"
        className="mt-6 block rounded-full bg-brand px-5 py-3 text-center text-white"
      >
        Continuar con el pedido
      </Link>
    </div>
  );
}
