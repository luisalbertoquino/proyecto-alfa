"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { RutinaProductosPicker } from "@/components/RutinaProductosPicker";
import { apiFetch, ApiRequestError } from "@/lib/api";
import type { Producto } from "@/types/admin";

export default function NuevaRutinaPage() {
  return (
    <ProtegerRuta>
      <FormularioNuevaRutina />
    </ProtegerRuta>
  );
}

function FormularioNuevaRutina() {
  const router = useRouter();
  const [productos, setProductos] = useState<Producto[]>([]);
  const [nombre, setNombre] = useState("");
  const [descripcion, setDescripcion] = useState("");
  const [seleccionados, setSeleccionados] = useState<number[]>([]);
  const [enviando, setEnviando] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    apiFetch<Producto[]>("/productos").then(setProductos);
  }, []);

  async function guardar(e: React.FormEvent) {
    e.preventDefault();
    setEnviando(true);
    setError(null);
    try {
      await apiFetch("/rutinas", {
        method: "POST",
        body: JSON.stringify({ nombre, descripcion, productos: seleccionados }),
      });
      router.push("/rutinas");
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos crear la rutina.");
    } finally {
      setEnviando(false);
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-semibold">Nueva rutina</h1>
      <form onSubmit={guardar} className="mt-6 space-y-4">
        <div className="max-w-lg">
          <label className="block text-sm font-medium">Nombre</label>
          <input
            required
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>
        <div className="max-w-lg">
          <label className="block text-sm font-medium">Descripción (opcional)</label>
          <textarea
            value={descripcion}
            onChange={(e) => setDescripcion(e.target.value)}
            rows={2}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>

        <RutinaProductosPicker
          productos={productos}
          seleccionados={seleccionados}
          onChange={setSeleccionados}
        />

        {error && <p className="text-sm text-red-600">{error}</p>}

        <button
          type="submit"
          disabled={enviando || seleccionados.length === 0}
          className="rounded-full bg-neutral-900 px-5 py-2 text-white disabled:opacity-50"
        >
          {enviando ? "Guardando..." : "Guardar"}
        </button>
      </form>
    </div>
  );
}
