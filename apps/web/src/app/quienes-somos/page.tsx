import { apiFetch } from "@/lib/api";
import type { Negocio } from "@/types/tienda";

export const metadata = { title: "Quiénes somos — Skincare Piloto" };

export default async function QuienesSomosPage() {
  const negocio = await apiFetch<Negocio>("/tienda/negocio");

  return (
    <article className="max-w-2xl">
      <h1 className="text-2xl font-semibold">Quiénes somos</h1>
      {negocio.quienes_somos ? (
        negocio.quienes_somos.split("\n\n").map((parrafo, i) => (
          <p key={i} className="mt-4 whitespace-pre-line text-neutral-700">
            {parrafo}
          </p>
        ))
      ) : (
        <p className="mt-4 text-neutral-500">
          Todavía no hay una historia publicada.
        </p>
      )}
    </article>
  );
}
