export const metadata = { title: "Contáctanos — Skincare Piloto" };

export default function ContactanosPage() {
  return (
    <article className="max-w-2xl">
      <h1 className="text-2xl font-semibold">Contáctanos</h1>
      <p className="mt-4 text-neutral-700">
        Todavía no tenemos un canal de contacto real conectado (WhatsApp,
        correo, horarios de atención) — este es un espacio reservado
        (placeholder) hasta que se defina cómo va a operar la atención al
        cliente del negocio.
      </p>
      <dl className="mt-6 space-y-3 text-sm">
        <div>
          <dt className="font-medium text-neutral-900">Canal de atención</dt>
          <dd className="text-neutral-600">Pendiente de definir.</dd>
        </div>
        <div>
          <dt className="font-medium text-neutral-900">Horarios de atención</dt>
          <dd className="text-neutral-600">Pendiente de definir.</dd>
        </div>
      </dl>
    </article>
  );
}
