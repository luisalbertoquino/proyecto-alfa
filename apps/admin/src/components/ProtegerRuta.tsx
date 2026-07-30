"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/context/AuthContext";

export function ProtegerRuta({ children }: { children: React.ReactNode }) {
  const { usuario, cargando } = useAuth();
  const router = useRouter();

  useEffect(() => {
    if (!cargando && !usuario) {
      router.replace("/login");
    }
  }, [cargando, usuario, router]);

  if (cargando) {
    return <p className="p-8 text-neutral-500">Cargando…</p>;
  }

  if (!usuario) {
    return null;
  }

  return <>{children}</>;
}
