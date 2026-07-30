"use client";

import { useState } from "react";
import type { Categoria, Necesidad, Producto } from "@/types/admin";

export type DatosProductoForm = {
  nombre: string;
  categoria_id: number | null;
  precio: string;
  stock: number;
  descripcion: string;
  sku: string;
  imagen: File | null;
  activo: boolean;
  destacado: boolean;
  necesidades: number[];
};

/**
 * Laravel no parsea multipart/form-data en peticiones PUT/PATCH reales (es
 * una limitación de PHP, no de Laravel) — por eso la actualización con
 * archivo va como POST con un campo _method=PATCH (method spoofing), que sí
 * funciona. Ver también apps/admin/src/lib/api.ts para el manejo de headers.
 */
export function construirFormData(
  datos: DatosProductoForm,
  { comoPatch = false }: { comoPatch?: boolean } = {},
): FormData {
  const formData = new FormData();

  if (comoPatch) formData.append("_method", "PATCH");
  formData.append("nombre", datos.nombre);
  if (datos.categoria_id !== null) {
    formData.append("categoria_id", String(datos.categoria_id));
  }
  formData.append("precio", datos.precio);
  formData.append("stock", String(datos.stock));
  formData.append("descripcion", datos.descripcion);
  formData.append("sku", datos.sku);
  formData.append("activo", datos.activo ? "1" : "0");
  formData.append("destacado", datos.destacado ? "1" : "0");
  datos.necesidades.forEach((id) => formData.append("necesidades[]", String(id)));
  if (datos.imagen) formData.append("imagen", datos.imagen);

  return formData;
}

export function ProductoForm({
  categorias,
  necesidades,
  producto,
  enviando,
  error,
  onSubmit,
}: {
  categorias: Categoria[];
  necesidades: Necesidad[];
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
  const [destacado, setDestacado] = useState(producto?.destacado ?? false);
  const [necesidadesElegidas, setNecesidadesElegidas] = useState<number[]>(
    producto?.necesidades?.map((n) => n.id) ?? [],
  );

  const [imagen, setImagen] = useState<File | null>(null);
  const [previaImagen, setPreviaImagen] = useState<string | null>(
    producto?.imagen_url ?? null,
  );

  function elegirImagen(archivo: File | null) {
    setImagen(archivo);
    setPreviaImagen(archivo ? URL.createObjectURL(archivo) : producto?.imagen_url ?? null);
  }

  function alternarNecesidad(id: number) {
    setNecesidadesElegidas((actual) =>
      actual.includes(id) ? actual.filter((n) => n !== id) : [...actual, id],
    );
  }

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
          imagen,
          activo,
          destacado,
          necesidades: necesidadesElegidas,
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
        <label className="block text-sm font-medium">Foto de portada (opcional)</label>
        <input
          type="file"
          accept="image/*"
          onChange={(e) => elegirImagen(e.target.files?.[0] ?? null)}
          className="mt-1 w-full text-sm"
        />
        <p className="mt-1 text-xs text-neutral-500">JPG o WEBP, máximo 4 MB.</p>
        {previaImagen && (
          // eslint-disable-next-line @next/next/no-img-element -- vista previa de un archivo local o de la foto ya guardada
          <img
            src={previaImagen}
            alt="Vista previa"
            className="mt-2 h-24 w-24 rounded object-cover"
          />
        )}
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

      {necesidades.length > 0 && (
        <div>
          <label className="block text-sm font-medium">
            Necesidades de piel (opcional)
          </label>
          <div className="mt-1 flex flex-wrap gap-2">
            {necesidades.map((n) => (
              <label
                key={n.id}
                className={`cursor-pointer rounded-full border px-3 py-1 text-sm ${
                  necesidadesElegidas.includes(n.id)
                    ? "border-neutral-900 bg-neutral-900 text-white"
                    : "border-neutral-300 text-neutral-700"
                }`}
              >
                <input
                  type="checkbox"
                  className="hidden"
                  checked={necesidadesElegidas.includes(n.id)}
                  onChange={() => alternarNecesidad(n.id)}
                />
                {n.nombre}
              </label>
            ))}
          </div>
        </div>
      )}

      <label className="flex items-center gap-2 text-sm">
        <input
          type="checkbox"
          checked={activo}
          onChange={(e) => setActivo(e.target.checked)}
        />
        Visible en la tienda
      </label>

      <label className="flex items-center gap-2 text-sm">
        <input
          type="checkbox"
          checked={destacado}
          onChange={(e) => setDestacado(e.target.checked)}
        />
        Destacado (aparece en &quot;Más vendidos&quot; del home)
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
