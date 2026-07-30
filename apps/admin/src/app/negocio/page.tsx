"use client";

import { useEffect, useState } from "react";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { apiFetch, ApiRequestError } from "@/lib/api";
import type { Negocio } from "@/types/admin";

export default function NegocioPage() {
  return (
    <ProtegerRuta>
      <FormularioNegocio />
    </ProtegerRuta>
  );
}

function FormularioNegocio() {
  const [negocio, setNegocio] = useState<Negocio | null>(null);
  const [quienesSomos, setQuienesSomos] = useState("");
  const [whatsapp, setWhatsapp] = useState("");
  const [email, setEmail] = useState("");
  const [horario, setHorario] = useState("");
  const [guardando, setGuardando] = useState(false);
  const [guardado, setGuardado] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    apiFetch<Negocio>("/negocio").then((n) => {
      setNegocio(n);
      setQuienesSomos(n.quienes_somos ?? "");
      setWhatsapp(n.contacto_whatsapp ?? "");
      setEmail(n.contacto_email ?? "");
      setHorario(n.contacto_horario ?? "");
    });
  }, []);

  async function guardar(e: React.FormEvent) {
    e.preventDefault();
    setGuardando(true);
    setGuardado(false);
    setError(null);
    try {
      await apiFetch("/negocio", {
        method: "PATCH",
        body: JSON.stringify({
          quienes_somos: quienesSomos,
          contacto_whatsapp: whatsapp,
          contacto_email: email,
          contacto_horario: horario,
        }),
      });
      setGuardado(true);
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos guardar los cambios.");
    } finally {
      setGuardando(false);
    }
  }

  if (!negocio) {
    return <p className="text-neutral-500">Cargando…</p>;
  }

  return (
    <div>
      <h1 className="text-2xl font-semibold">Configuración del negocio</h1>
      <p className="mt-1 text-sm text-neutral-600">
        Este contenido se muestra en las páginas &quot;Quiénes somos&quot; y
        &quot;Contáctanos&quot; de la tienda.
      </p>

      <form onSubmit={guardar} className="mt-6 max-w-lg space-y-4">
        <div>
          <label className="block text-sm font-medium">Quiénes somos</label>
          <textarea
            value={quienesSomos}
            onChange={(e) => setQuienesSomos(e.target.value)}
            rows={8}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
          <p className="mt-1 text-xs text-neutral-500">
            Un párrafo por línea en blanco entre ellos.
          </p>
        </div>

        <div>
          <label className="block text-sm font-medium">WhatsApp</label>
          <input
            value={whatsapp}
            onChange={(e) => setWhatsapp(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>

        <div>
          <label className="block text-sm font-medium">Email de contacto</label>
          <input
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>

        <div>
          <label className="block text-sm font-medium">Horario de atención</label>
          <input
            value={horario}
            onChange={(e) => setHorario(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>

        {error && <p className="text-sm text-red-600">{error}</p>}
        {guardado && <p className="text-sm text-green-700">Guardado.</p>}

        <button
          type="submit"
          disabled={guardando}
          className="rounded-full bg-neutral-900 px-5 py-2 text-white disabled:opacity-50"
        >
          {guardando ? "Guardando..." : "Guardar"}
        </button>
      </form>
    </div>
  );
}
