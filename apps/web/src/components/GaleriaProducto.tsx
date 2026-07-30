"use client";

import { useState } from "react";

export function GaleriaProducto({
  nombre,
  imagenPortada,
  imagenesGaleria,
}: {
  nombre: string;
  imagenPortada: string | null;
  imagenesGaleria: string[];
}) {
  const todas = [imagenPortada, ...imagenesGaleria].filter(
    (url): url is string => Boolean(url),
  );
  const [activa, setActiva] = useState(todas[0] ?? null);

  if (!activa) return null;

  return (
    <div>
      <div className="aspect-square w-full overflow-hidden rounded-lg bg-neutral-100">
        {/* eslint-disable-next-line @next/next/no-img-element -- imagen externa o del disco propio, sin necesidad del optimizador de Next para un prototipo */}
        <img src={activa} alt={nombre} className="h-full w-full object-cover" />
      </div>

      {todas.length > 1 && (
        <div className="mt-3 flex gap-2">
          {todas.map((url) => (
            <button
              key={url}
              type="button"
              onClick={() => setActiva(url)}
              className={`h-16 w-16 overflow-hidden rounded border ${
                url === activa ? "border-neutral-900" : "border-neutral-200"
              }`}
            >
              {/* eslint-disable-next-line @next/next/no-img-element -- miniatura de la galería */}
              <img src={url} alt="" className="h-full w-full object-cover" />
            </button>
          ))}
        </div>
      )}
    </div>
  );
}
