import type { ApiError } from "@/types/tienda";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api/v1";

export class ApiRequestError extends Error {
  constructor(
    public codigo: string,
    mensaje: string,
    public detalles: Record<string, unknown>,
  ) {
    super(mensaje);
  }
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
  const res = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...options.headers,
    },
  });

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
