"use client";

import { useState } from "react";
import type { Categoria, Producto } from "@/types/admin";

export type DatosProductoForm = {
  nombre: string;
  categoria_id: number | null;
  precio: string;
  stock: number;
  descripcion: string;
  sku: string;
  activo: boolean;
};

export function ProductoForm({
  categorias,
  producto,
  enviando,
  error,
  onSubmit,
}: {
  categorias: Categoria[];
  producto?: Producto;
  enviando: boolean;
  error: string | null;
  onSubmit: (datos: DatosProductoForm) => void;
}) {
  const [nombre, setNombre] = useState(producto?.nombre ?? "");
  const [categoriaId, setCategoriaId] = useState<number | null>(
    producto?.categoria_id ?? null,
  );
  const [precio, setPrecio] = useState(producto?.precio ?? "");
  const [stock, setStock] = useState(producto?.stock ?? 0);
  const [descripcion, setDescripcion] = useState(producto?.descripcion ?? "");
  const [sku, setSku] = useState(producto?.sku ?? "");
  const [activo, setActivo] = useState(producto?.activo ?? true);

  return (
    <form
      onSubmit={(e) => {
        e.preventDefault();
        onSubmit({
          nombre,
          categoria_id: categoriaId,
          precio,
          stock,
          descripcion,
          sku,
          activo,
        });
      }}
      className="max-w-lg space-y-4"
    >
      <div>
        <label className="block text-sm font-medium">Nombre</label>
        <input
          required
          value={nombre}
          onChange={(e) => setNombre(e.target.value)}
          className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
        />
      </div>

      <div>
        <label className="block text-sm font-medium">Categoría</label>
        <select
          value={categoriaId ?? ""}
          onChange={(e) =>
            setCategoriaId(e.target.value ? Number(e.target.value) : null)
          }
          className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
        >
          <option value="">Sin categoría</option>
          {categorias.map((c) => (
            <option key={c.id} value={c.id}>
              {c.nombre}
            </option>
          ))}
        </select>
      </div>

      <div className="flex gap-4">
        <div className="flex-1">
          <label className="block text-sm font-medium">Precio (COP)</label>
          <input
            required
            type="number"
            min={0}
            value={precio}
            onChange={(e) => setPrecio(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>
        <div className="flex-1">
          <label className="block text-sm font-medium">Stock</label>
          <input
            required
            type="number"
            min={0}
            value={stock}
            onChange={(e) => setStock(Number(e.target.value))}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>
      </div>

      <div>
        <label className="block text-sm font-medium">SKU (opcional)</label>
        <input
          value={sku}
          onChange={(e) => setSku(e.target.value)}
          className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
        />
      </div>

      <div>
        <label className="block text-sm font-medium">
          Descripción (opcional)
        </label>
        <textarea
          value={descripcion}
          onChange={(e) => setDescripcion(e.target.value)}
          rows={3}
          className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
        />
      </div>

      <label className="flex items-center gap-2 text-sm">
        <input
          type="checkbox"
          checked={activo}
          onChange={(e) => setActivo(e.target.checked)}
        />
        Visible en la tienda
      </label>

      {error && <p className="text-sm text-red-600">{error}</p>}

      <button
        type="submit"
        disabled={enviando}
        className="rounded-full bg-neutral-900 px-5 py-2 text-white disabled:opacity-50"
      >
        {enviando ? "Guardando..." : "Guardar"}
      </button>
    </form>
  );
}
