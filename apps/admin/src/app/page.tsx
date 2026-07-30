"use client";

import { useEffect } from "react";
import { useRouter } from "next/navigation";
import { useAuth } from "@/context/AuthContext";

export default function Home() {
  const router = useRouter();
  const { usuario, cargando } = useAuth();

  useEffect(() => {
    if (!cargando) {
      router.replace(usuario ? "/pedidos" : "/login");
    }
  }, [cargando, usuario, router]);

  return null;
}
