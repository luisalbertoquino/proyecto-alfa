"use client";

import Link from "next/link";
import { useCarrito } from "@/context/CarritoContext";

export function Header() {
  const { cantidadTotal } = useCarrito();

  return (
    <header className="border-b border-neutral-200">
      <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <Link href="/" className="text-lg font-semibold">
          Skincare Piloto
        </Link>
        <Link
          href="/carrito"
          className="rounded-full bg-neutral-900 px-4 py-2 text-sm text-white"
        >
          Carrito {cantidadTotal > 0 ? `(${cantidadTotal})` : ""}
        </Link>
      </div>
    </header>
  );
}
