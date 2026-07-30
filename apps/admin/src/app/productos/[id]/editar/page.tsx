"use client";

import { useEffect, useState } from "react";
import { useParams, useRouter } from "next/navigation";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { ProductoForm, construirFormData, type DatosProductoForm } from "@/components/ProductoForm";
import { apiFetch, ApiRequestError } from "@/lib/api";
import type { Categoria, Producto } from "@/types/admin";

export default function EditarProductoPage() {
  return (
    <ProtegerRuta>
      <FormularioEditarProducto />
    </ProtegerRuta>
  );
}

function FormularioEditarProducto() {
  const { id } = useParams<{ id: string }>();
  const router = useRouter();
  const [producto, setProducto] = useState<Producto | null>(null);
  const [categorias, setCategorias] = useState<Categoria[]>([]);
  const [enviando, setEnviando] = useState(false);
  const [error, setError] = useState<string | null>(null);

  useEffect(() => {
    Promise.all([
      apiFetch<Producto>(`/productos/${id}`),
      apiFetch<Categoria[]>("/categorias"),
    ]).then(([p, c]) => {
      setProducto(p);
      setCategorias(c);
    });
  }, [id]);

  async function guardar(datos: DatosProductoForm) {
    setEnviando(true);
    setError(null);
    try {
      // POST + _method=PATCH (method spoofing): PHP no parsea multipart en
      // peticiones PATCH reales, así que la actualización con archivo tiene
      // que viajar como POST — ver construirFormData en ProductoForm.tsx.
      await apiFetch(`/productos/${id}`, {
        method: "POST",
        body: construirFormData(datos, { comoPatch: true }),
      });
      router.push("/productos");
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos guardar los cambios.");
    } finally {
      setEnviando(false);
    }
  }

  if (!producto) {
    return <p className="text-neutral-500">Cargando…</p>;
  }

  return (
    <div>
      <h1 className="text-2xl font-semibold">Editar producto</h1>
      <div className="mt-6">
        <ProductoForm
          categorias={categorias}
          producto={producto}
          enviando={enviando}
          error={error}
          onSubmit={guardar}
        />
      </div>
    </div>
  );
}
