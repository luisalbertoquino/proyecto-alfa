import type { ApiError } from "@/types/admin";

const API_URL = process.env.NEXT_PUBLIC_API_URL ?? "http://127.0.0.1:8000/api/v1";
const CLAVE_TOKEN = "proyecto-alfa-admin-token";

export class ApiRequestError extends Error {
  constructor(
    public codigo: string,
    mensaje: string,
    public status: number,
    public detalles: Record<string, unknown>,
  ) {
    super(mensaje);
  }
}

export function getToken(): string | null {
  if (typeof window === "undefined") return null;
  return window.localStorage.getItem(CLAVE_TOKEN);
}

export function setToken(token: string) {
  window.localStorage.setItem(CLAVE_TOKEN, token);
}

export function clearToken() {
  window.localStorage.removeItem(CLAVE_TOKEN);
}

/**
 * Cliente de la API para el panel (apps/admin es CSR — ver
 * docs/architecture/arquitectura-frontend.md). Adjunta el token de Sanctum
 * en cada petición y traduce el envelope de error de
 * docs/architecture/apis.md a una excepción tipada.
 */
export async function apiFetch<T>(
  path: string,
  options: RequestInit = {},
): Promise<T> {
  const token = getToken();

  const res = await fetch(`${API_URL}${path}`, {
    ...options,
    headers: {
      Accept: "application/json",
      "Content-Type": "application/json",
      ...(token ? { Authorization: `Bearer ${token}` } : {}),
      ...options.headers,
    },
  });

  if (res.status === 204) {
    return undefined as T;
  }

  const body = await res.json();

  if (!res.ok) {
    const err = body as ApiError;
    throw new ApiRequestError(
      err.error?.codigo ?? "ERROR_DESCONOCIDO",
      err.error?.mensaje ?? "Ocurrió un error inesperado.",
      res.status,
      err.error?.detalles ?? {},
    );
  }

  return (body as { data: T }).data;
}
