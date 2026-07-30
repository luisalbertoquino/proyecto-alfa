"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { apiFetch } from "@/lib/api";
import type { Producto } from "@/types/admin";

function formatearPrecio(valor: string) {
  return new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "COP",
    maximumFractionDigits: 0,
  }).format(Number(valor));
}

export default function ProductosPage() {
  return (
    <ProtegerRuta>
      <ListaProductos />
    </ProtegerRuta>
  );
}

function ListaProductos() {
  const [productos, setProductos] = useState<Producto[] | null>(null);
  const [error, setError] = useState<string | null>(null);

  async function cargar() {
    try {
      const data = await apiFetch<Producto[]>("/productos");
      setProductos(data);
    } catch {
      setError("No pudimos cargar los productos.");
    }
  }

  useEffect(() => {
    cargar();
  }, []);

  async function eliminar(producto: Producto) {
    if (!confirm(`¿Eliminar "${producto.nombre}"? Esta acción no se puede deshacer.`)) {
      return;
    }
    await apiFetch(`/productos/${producto.id}`, { method: "DELETE" });
    cargar();
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Productos</h1>
        <Link
          href="/productos/nuevo"
          className="rounded-full bg-neutral-900 px-4 py-2 text-sm text-white"
        >
          Nuevo producto
        </Link>
      </div>

      {error && <p className="mt-4 text-red-600">{error}</p>}

      {!productos ? (
        <p className="mt-8 text-neutral-500">Cargando…</p>
      ) : productos.length === 0 ? (
        <p className="mt-8 text-neutral-500">Todavía no hay productos.</p>
      ) : (
        <table className="mt-6 w-full border-collapse text-sm">
          <thead>
            <tr className="border-b border-neutral-200 text-left text-neutral-500">
              <th className="py-2"></th>
              <th className="py-2">Nombre</th>
              <th className="py-2">Categoría</th>
              <th className="py-2">Precio</th>
              <th className="py-2">Stock</th>
              <th className="py-2">Visible</th>
              <th className="py-2"></th>
            </tr>
          </thead>
          <tbody>
            {productos.map((p) => (
              <tr key={p.id} className="border-b border-neutral-100">
                <td className="py-2">
                  {p.imagen_url ? (
                    // eslint-disable-next-line @next/next/no-img-element -- imagen externa (Unsplash), sin necesidad del optimizador de Next para un prototipo
                    <img
                      src={p.imagen_url}
                      alt={p.nombre}
                      className="h-10 w-10 rounded object-cover"
                    />
                  ) : (
                    <div className="h-10 w-10 rounded bg-neutral-100" />
                  )}
                </td>
                <td className="py-2 font-medium">{p.nombre}</td>
                <td className="py-2 text-neutral-600">
                  {p.categoria?.nombre ?? "—"}
                </td>
                <td className="py-2">{formatearPrecio(p.precio)}</td>
                <td className="py-2">{p.stock}</td>
                <td className="py-2">{p.activo ? "Sí" : "No"}</td>
                <td className="py-2 text-right">
                  <Link
                    href={`/productos/${p.id}/editar`}
                    className="mr-4 underline"
                  >
                    Editar
                  </Link>
                  <button
                    onClick={() => eliminar(p)}
                    className="text-red-600 underline"
                  >
                    Eliminar
                  </button>
                </td>
              </tr>
            ))}
          </tbody>
        </table>
      )}
    </div>
  );
}
