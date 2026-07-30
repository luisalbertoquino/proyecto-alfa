"use client";

import { useEffect, useState } from "react";
import Link from "next/link";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { apiFetch } from "@/lib/api";
import type { Rutina } from "@/types/admin";

export default function RutinasPage() {
  return (
    <ProtegerRuta>
      <ListaRutinas />
    </ProtegerRuta>
  );
}

function ListaRutinas() {
  const [rutinas, setRutinas] = useState<Rutina[] | null>(null);

  async function cargar() {
    setRutinas(await apiFetch<Rutina[]>("/rutinas"));
  }

  useEffect(() => {
    cargar();
  }, []);

  async function eliminar(rutina: Rutina) {
    if (!confirm(`¿Eliminar "${rutina.nombre}"?`)) return;
    await apiFetch(`/rutinas/${rutina.id}`, { method: "DELETE" });
    cargar();
  }

  return (
    <div>
      <div className="flex items-center justify-between">
        <h1 className="text-2xl font-semibold">Rutinas sugeridas</h1>
        <Link
          href="/rutinas/nueva"
          className="rounded-full bg-neutral-900 px-4 py-2 text-sm text-white"
        >
          Nueva rutina
        </Link>
      </div>

      {!rutinas ? (
        <p className="mt-8 text-neutral-500">Cargando…</p>
      ) : rutinas.length === 0 ? (
        <p className="mt-8 text-neutral-500">Todavía no hay rutinas.</p>
      ) : (
        <ul className="mt-6 space-y-3">
          {rutinas.map((r) => (
            <li
              key={r.id}
              className="flex items-center justify-between rounded-lg border border-neutral-200 p-4"
            >
              <div>
                <p className="font-medium">{r.nombre}</p>
                <p className="text-sm text-neutral-600">
                  {r.productos.length} paso{r.productos.length === 1 ? "" : "s"}
                </p>
              </div>
              <div>
                <Link href={`/rutinas/${r.id}/editar`} className="mr-4 underline">
                  Editar
                </Link>
                <button onClick={() => eliminar(r)} className="text-red-600 underline">
                  Eliminar
                </button>
              </div>
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
