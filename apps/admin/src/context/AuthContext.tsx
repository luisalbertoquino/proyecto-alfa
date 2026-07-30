"use client";

import {
  createContext,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import { useRouter } from "next/navigation";
import { apiFetch, clearToken, getToken, setToken } from "@/lib/api";
import type { Usuario } from "@/types/admin";

type AuthContextValor = {
  usuario: Usuario | null;
  cargando: boolean;
  login: (email: string, password: string) => Promise<void>;
  logout: () => Promise<void>;
};

const AuthContext = createContext<AuthContextValor | null>(null);

export function AuthProvider({ children }: { children: ReactNode }) {
  const [usuario, setUsuario] = useState<Usuario | null>(null);
  const [cargando, setCargando] = useState(true);
  const router = useRouter();

  useEffect(() => {
    async function cargarSesion() {
      if (!getToken()) {
        setCargando(false);
        return;
      }
      try {
        const me = await apiFetch<Usuario>("/me");
        setUsuario(me);
      } catch {
        clearToken();
      } finally {
        setCargando(false);
      }
    }
    cargarSesion();
  }, []);

  async function login(email: string, password: string) {
    const respuesta = await apiFetch<{ token: string; usuario: Usuario }>(
      "/login",
      { method: "POST", body: JSON.stringify({ email, password }) },
    );
    setToken(respuesta.token);
    setUsuario(respuesta.usuario);
    router.push("/pedidos");
  }

  async function logout() {
    try {
      await apiFetch("/logout", { method: "POST" });
    } catch {
      // si el token ya no es válido, igual limpiamos localmente.
    }
    clearToken();
    setUsuario(null);
    router.push("/login");
  }

  return (
    <AuthContext.Provider value={{ usuario, cargando, login, logout }}>
      {children}
    </AuthContext.Provider>
  );
}

export function useAuth() {
  const contexto = useContext(AuthContext);
  if (!contexto) {
    throw new Error("useAuth debe usarse dentro de un AuthProvider");
  }
  return contexto;
}
