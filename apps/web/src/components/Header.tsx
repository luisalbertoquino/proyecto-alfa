"use client";

import Link from "next/link";
import { useCarrito } from "@/context/CarritoContext";

export function Header({ nombre }: { nombre: string }) {
  const { cantidadTotal } = useCarrito();

  return (
    <header className="border-b border-neutral-200">
      <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <Link href="/" className="text-lg font-semibold">
          {nombre}
        </Link>
        <nav className="hidden gap-6 text-sm text-neutral-600 sm:flex">
          <Link href="/" className="hover:text-neutral-900">
            Tienda
          </Link>
          <Link href="/rutinas" className="hover:text-neutral-900">
            Rutinas
          </Link>
          <Link href="/quienes-somos" className="hover:text-neutral-900">
            Quiénes somos
          </Link>
          <Link href="/contactanos" className="hover:text-neutral-900">
            Contáctanos
          </Link>
        </nav>
        <Link
          href="/carrito"
          className="rounded-full bg-brand px-4 py-2 text-sm text-white"
        >
          Carrito {cantidadTotal > 0 ? `(${cantidadTotal})` : ""}
        </Link>
      </div>
    </header>
  );
}
