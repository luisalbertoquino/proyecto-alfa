"use client";

import type { Producto } from "@/types/admin";

export function RutinaProductosPicker({
  productos,
  seleccionados,
  onChange,
}: {
  productos: Producto[];
  seleccionados: number[];
  onChange: (idsOrdenados: number[]) => void;
}) {
  function alternar(id: number) {
    onChange(
      seleccionados.includes(id)
        ? seleccionados.filter((x) => x !== id)
        : [...seleccionados, id],
    );
  }

  function mover(indice: number, direccion: -1 | 1) {
    const destino = indice + direccion;
    if (destino < 0 || destino >= seleccionados.length) return;
    const copia = [...seleccionados];
    [copia[indice], copia[destino]] = [copia[destino], copia[indice]];
    onChange(copia);
  }

  const porId = new Map(productos.map((p) => [p.id, p]));

  return (
    <div className="grid gap-6 sm:grid-cols-2">
      <div>
        <p className="text-sm font-medium">Productos disponibles</p>
        <div className="mt-2 max-h-80 space-y-1 overflow-y-auto rounded border border-neutral-200 p-2">
          {productos.map((p) => (
            <label key={p.id} className="flex items-center gap-2 py-1 text-sm">
              <input
                type="checkbox"
                checked={seleccionados.includes(p.id)}
                onChange={() => alternar(p.id)}
              />
              {p.nombre}
            </label>
          ))}
        </div>
      </div>

      <div>
        <p className="text-sm font-medium">
          Orden de la rutina ({seleccionados.length} paso
          {seleccionados.length === 1 ? "" : "s"})
        </p>
        <ol className="mt-2 space-y-1 rounded border border-neutral-200 p-2">
          {seleccionados.length === 0 && (
            <p className="text-sm text-neutral-500">
              Marca productos a la izquierda.
            </p>
          )}
          {seleccionados.map((id, i) => (
            <li key={id} className="flex items-center gap-2 text-sm">
              <span className="w-5 text-neutral-500">{i + 1}.</span>
              <span className="flex-1">{porId.get(id)?.nombre ?? `#${id}`}</span>
              <button
                type="button"
                onClick={() => mover(i, -1)}
                disabled={i === 0}
                className="disabled:opacity-30"
                aria-label="Subir"
              >
                ↑
              </button>
              <button
                type="button"
                onClick={() => mover(i, 1)}
                disabled={i === seleccionados.length - 1}
                className="disabled:opacity-30"
                aria-label="Bajar"
              >
                ↓
              </button>
            </li>
          ))}
        </ol>
      </div>
    </div>
  );
}
