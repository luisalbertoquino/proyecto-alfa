"use client";

import { useState } from "react";
import { useRouter } from "next/navigation";
import type { Producto } from "@/types/tienda";
import { useCarrito } from "@/context/CarritoContext";

export function AgregarAlCarrito({ producto }: { producto: Producto }) {
  const { agregar } = useCarrito();
  const router = useRouter();
  const [cantidad, setCantidad] = useState(1);
  const [agregado, setAgregado] = useState(false);

  if (producto.stock === 0) {
    return <p className="mt-4 font-medium text-red-600">Agotado por ahora.</p>;
  }

  return (
    <div className="mt-6 flex items-center gap-3">
      <input
        type="number"
        min={1}
        max={producto.stock}
        value={cantidad}
        onChange={(e) =>
          setCantidad(
            Math.max(1, Math.min(producto.stock, Number(e.target.value) || 1)),
          )
        }
        className="w-16 rounded border border-neutral-300 px-2 py-2 text-center"
      />
      <button
        onClick={() => {
          agregar(producto, cantidad);
          setAgregado(true);
        }}
        className="rounded-full bg-neutral-900 px-5 py-2 text-sm text-white"
      >
        Agregar al carrito
      </button>
      {agregado && (
        <button
          onClick={() => router.push("/carrito")}
          className="text-sm underline"
        >
          Ver carrito
        </button>
      )}
    </div>
  );
}
