import type { ApiError } from "@/types/tienda";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api/v1";

// Solo para previsualizar en local un tenant que en producción tendría su
// propio subdominio de API real (ver App\Shared\Http\Middleware\ResolvePublicTenant
// y tenants.dominio_api) — en producción cada tenant ya tiene su propio
// NEXT_PUBLIC_API_URL apuntando a su propio subdominio, sin necesitar este
// truco. Solo aplica a las peticiones que corren en el servidor de Next.js
// (Server Components) — un navegador nunca deja mandar un header Host
// manual, ver apps/web/README.md.
const TENANT_API_HOST = process.env.TENANT_API_HOST;

export class ApiRequestError extends Error {
  constructor(
    public codigo: string,
    mensaje: string,
    public detalles: Record<string, unknown>,
  ) {
    super(mensaje);
  }
}

type RespuestaMinima = { ok: boolean; json: () => Promise<unknown> };

/**
 * fetch() (navegador o Node) ignora un header Host puesto a mano — está en
 * la lista de headers "prohibidos" del estándar Fetch, confirmado con una
 * prueba directa. Para previsualizar un tenant por dominio en local hace
 * falta el módulo http/https de Node, que sí lo respeta.
 */
async function fetchConHostPersonalizado(
  url: string,
  options: RequestInit,
  host: string,
): Promise<RespuestaMinima> {
  const { request } = url.startsWith("https:")
    ? await import("node:https")
    : await import("node:http");
  const parsed = new URL(url);

  return new Promise((resolve, reject) => {
    const req = request(
      {
        hostname: parsed.hostname,
        port: parsed.port,
        path: `${parsed.pathname}${parsed.search}`,
        method: options.method ?? "GET",
        headers: { ...(options.headers as Record<string, string>), Host: host },
      },
      (res: import("http").IncomingMessage) => {
        let body = "";
        res.on("data", (chunk) => (body += chunk));
        res.on("end", () => {
          const status = res.statusCode ?? 500;
          resolve({
            ok: status >= 200 && status < 300,
            json: async () => JSON.parse(body),
          });
        });
      },
    );
    req.on("error", reject);
    if (options.body) req.write(options.body as string);
    req.end();
  });
}

/**
 * Cliente mínimo de la API propia (docs/architecture/vision-tecnica.md:
 * apps/web nunca consulta otra cosa que no sea esta API). Traduce el
 * envelope de error { error: { codigo, mensaje, detalles } } de
 * docs/architecture/apis.md a una excepción tipada.
 */
export async function apiFetch<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const headers = {
    Accept: "application/json",
    "Content-Type": "application/json",
    ...options.headers,
  };

  const res =
    TENANT_API_HOST && typeof window === "undefined"
      ? await fetchConHostPersonalizado(`${API_URL}${path}`, { ...options, headers }, TENANT_API_HOST)
      : await fetch(`${API_URL}${path}`, { ...options, headers });

  const body = await res.json();

  if (!res.ok) {
    const err = body as ApiError;
    throw new ApiRequestError(
      err.error?.codigo ?? "ERROR_DESCONOCIDO",
      err.error?.mensaje ?? "Ocurrió un error inesperado.",
      err.error?.detalles ?? {},
    );
  }

  return (body as { data: T }).data;
}
