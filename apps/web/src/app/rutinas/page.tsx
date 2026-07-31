import Link from "next/link";
import { apiFetch } from "@/lib/api";
import { formatearPrecio } from "@/lib/formato";
import type { Rutina } from "@/types/tienda";

export const metadata = { title: "Rutinas sugeridas — Skincare Piloto" };
export const dynamic = "force-dynamic";

export default async function RutinasPage() {
  const rutinas = await apiFetch<Rutina[]>("/tienda/rutinas");

  return (
    <div>
      <h1 className="text-2xl font-semibold">Rutinas sugeridas</h1>
      <p className="mt-1 text-neutral-600">
        No sabes por dónde empezar? Elige según cuánto tiempo le quieras
        dedicar a tu piel.
      </p>

      <div className="mt-8 space-y-10">
        {rutinas.map((rutina) => {
          const total = rutina.productos.reduce(
            (suma, p) => suma + Number(p.precio),
            0,
          );

          return (
            <section key={rutina.id}>
              <div className="flex items-baseline justify-between">
                <h2 className="text-lg font-semibold">{rutina.nombre}</h2>
                <span className="text-sm text-neutral-500">
                  Total: {formatearPrecio(total)}
                </span>
              </div>
              {rutina.descripcion && (
                <p className="mt-1 text-sm text-neutral-600">{rutina.descripcion}</p>
              )}

              <ol className="mt-4 space-y-3">
                {rutina.productos.map((producto, i) => (
                  <li key={producto.id}>
                    <Link
                      href={`/productos/${producto.slug}`}
                      className="flex items-center gap-3 rounded-lg border border-neutral-200 p-3 hover:border-neutral-400"
                    >
                      <span className="flex h-6 w-6 flex-shrink-0 items-center justify-center rounded-full bg-neutral-900 text-xs text-white">
                        {i + 1}
                      </span>
                      {producto.imagen_url && (
                        <div className="h-12 w-12 flex-shrink-0 overflow-hidden rounded bg-neutral-100">
                          {/* eslint-disable-next-line @next/next/no-img-element -- imagen externa o del disco propio */}
                          <img
                            src={producto.imagen_url}
                            alt={producto.nombre}
                            className="h-full w-full object-cover"
                          />
                        </div>
                      )}
                      <span className="flex-1">
                        <span className="block text-sm font-medium">
                          {producto.nombre}
                        </span>
                        <span className="block text-xs text-neutral-500">
                          {formatearPrecio(producto.precio)}
                        </span>
                      </span>
                    </Link>
                  </li>
                ))}
              </ol>
            </section>
          );
        })}
      </div>
    </div>
  );
}
