import { apiFetch } from "@/lib/api";
import type { Negocio } from "@/types/tienda";

export const metadata = { title: "Contáctanos — Skincare Piloto" };

export default async function ContactanosPage() {
  const negocio = await apiFetch<Negocio>("/tienda/negocio");

  return (
    <article className="max-w-2xl">
      <h1 className="text-2xl font-semibold">Contáctanos</h1>
      <dl className="mt-6 space-y-4 text-sm">
        <div>
          <dt className="font-medium text-neutral-900">WhatsApp</dt>
          <dd className="text-neutral-600">
            {negocio.contacto_whatsapp ?? "Pendiente de definir."}
          </dd>
        </div>
        <div>
          <dt className="font-medium text-neutral-900">Email</dt>
          <dd className="text-neutral-600">
            {negocio.contacto_email ?? "Pendiente de definir."}
          </dd>
        </div>
        <div>
          <dt className="font-medium text-neutral-900">Horario de atención</dt>
          <dd className="text-neutral-600">
            {negocio.contacto_horario ?? "Pendiente de definir."}
          </dd>
        </div>
      </dl>
    </article>
  );
}
