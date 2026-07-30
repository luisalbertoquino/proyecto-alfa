"use client";

import { useState } from "react";
import { apiFetch, ApiRequestError } from "@/lib/api";
import type { ImagenProducto } from "@/types/admin";

export function GaleriaProductoManager({
  productoId,
  imagenesIniciales,
}: {
  productoId: number;
  imagenesIniciales: ImagenProducto[];
}) {
  const [imagenes, setImagenes] = useState(imagenesIniciales);
  const [subiendo, setSubiendo] = useState(false);
  const [error, setError] = useState<string | null>(null);

  async function agregarFotos(archivos: FileList | null) {
    if (!archivos || archivos.length === 0) return;
    setSubiendo(true);
    setError(null);
    try {
      const formData = new FormData();
      Array.from(archivos).forEach((archivo) => formData.append("imagenes[]", archivo));

      const nuevas = await apiFetch<ImagenProducto[]>(`/productos/${productoId}/imagenes`, {
        method: "POST",
        body: formData,
      });
      setImagenes((actual) => [...actual, ...nuevas]);
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos subir las fotos.");
    } finally {
      setSubiendo(false);
    }
  }

  async function eliminarFoto(imagenId: number) {
    if (!confirm("¿Quitar esta foto de la galería?")) return;
    await apiFetch(`/productos/${productoId}/imagenes/${imagenId}`, { method: "DELETE" });
    setImagenes((actual) => actual.filter((i) => i.id !== imagenId));
  }

  return (
    <div className="max-w-lg">
      <label className="block text-sm font-medium">
        Galería adicional (opcional)
      </label>
      <p className="mt-1 text-xs text-neutral-500">
        Hasta 8 fotos extra: textura, empaque, aplicación. Se guardan al
        elegirlas, no hace falta darle a &quot;Guardar&quot;.
      </p>

      {imagenes.length > 0 && (
        <div className="mt-3 flex flex-wrap gap-3">
          {imagenes.map((img) => (
            <div key={img.id} className="relative">
              {/* eslint-disable-next-line @next/next/no-img-element -- foto ya subida al servidor propio */}
              <img
                src={img.url}
                alt="Foto de la galería"
                className="h-20 w-20 rounded object-cover"
              />
              <button
                type="button"
                onClick={() => eliminarFoto(img.id)}
                className="absolute -right-2 -top-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-600 text-xs text-white"
                aria-label="Quitar foto"
              >
                ×
              </button>
            </div>
          ))}
        </div>
      )}

      <input
        type="file"
        accept="image/*"
        multiple
        disabled={subiendo}
        onChange={(e) => agregarFotos(e.target.files)}
        className="mt-3 w-full text-sm"
      />
      {subiendo && <p className="mt-1 text-xs text-neutral-500">Subiendo…</p>}
      {error && <p className="mt-1 text-sm text-red-600">{error}</p>}
    </div>
  );
}
