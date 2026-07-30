"use client";

import { useEffect, useState } from "react";
import { useRouter } from "next/navigation";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { ProductoForm, construirFormData, type DatosProductoForm } from "@/components/ProductoForm";
import { apiFetch, ApiRequestError } from "@/lib/api";
import type { Categoria } from "@/types/admin";

export default function NuevoProductoPage() {
  return (
    <ProtegerRuta>
      <FormularioNuevoProducto />
    </ProtegerRuta>
  );
}

function FormularioNuevoProducto() {
  const router = useRouter();
  const [categorias, setCategorias] = useState<Categoria[]>([]);
  const [enviando, setEnviando] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    apiFetch<Categoria[]>("/categorias").then(setCategorias);
  }, []);

  async function guardar(datos: DatosProductoForm) {
    setEnviando(true);
    setError(null);
    try {
      await apiFetch("/productos", {
        method: "POST",
        body: construirFormData(datos),
      });
      router.push("/productos");
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos crear el producto.");
    } finally {
      setEnviando(false);
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-semibold">Nuevo producto</h1>
      <div className="mt-6">
        <ProductoForm
          categorias={categorias}
          enviando={enviando}
          error={error}
          onSubmit={guardar}
        />
      </div>
    </div>
  );
}
