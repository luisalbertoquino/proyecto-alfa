"use client";

import { useState } from "react";
import { useAuth } from "@/context/AuthContext";
import { ApiRequestError } from "@/lib/api";

export default function LoginPage() {
  const { login } = useAuth();
  const [email, setEmail] = useState("");
  const [password, setPassword] = useState("");
  const [error, setError] = useState<string | null>(null);
  const [enviando, setEnviando] = useState(false);

  async function enviar(e: React.FormEvent) {
    e.preventDefault();
    setEnviando(true);
    setError(null);
    try {
      await login(email, password);
    } catch (err) {
      setError(
        err instanceof ApiRequestError
          ? err.message
          : "No pudimos iniciar sesión. Intenta de nuevo.",
      );
    } finally {
      setEnviando(false);
    }
  }

  return (
    <div className="mx-auto mt-16 max-w-sm">
      <h1 className="text-2xl font-semibold">Panel administrativo</h1>
      <p className="mt-1 text-neutral-600">Skincare Piloto</p>

      <form onSubmit={enviar} className="mt-8 space-y-4">
        <div>
          <label className="block text-sm font-medium">Email</label>
          <input
            required
            type="email"
            value={email}
            onChange={(e) => setEmail(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>
        <div>
          <label className="block text-sm font-medium">Contraseña</label>
          <input
            required
            type="password"
            value={password}
            onChange={(e) => setPassword(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>

        {error && <p className="text-sm text-red-600">{error}</p>}

        <button
          type="submit"
          disabled={enviando}
          className="w-full rounded-full bg-neutral-900 px-5 py-3 text-white disabled:opacity-50"
        >
          {enviando ? "Entrando..." : "Entrar"}
        </button>
      </form>
    </div>
  );
}
