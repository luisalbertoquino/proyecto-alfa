"use client";

import { useEffect, useState } from "react";
import { ProtegerRuta } from "@/components/ProtegerRuta";
import { apiFetch, ApiRequestError } from "@/lib/api";
import { ETIQUETAS_ESTADO, type Pedido } from "@/types/admin";

function formatearPrecio(valor: string) {
  return new Intl.NumberFormat("es-CO", {
    style: "currency",
    currency: "COP",
    maximumFractionDigits: 0,
  }).format(Number(valor));
}

const FILTROS = [
  { valor: "", texto: "Todos" },
  { valor: "pendiente_pago", texto: "Pendientes de pago" },
  { valor: "confirmado", texto: "Confirmados" },
  { valor: "cancelado", texto: "Cancelados" },
];

export default function PedidosPage() {
  return (
    <ProtegerRuta>
      <ListaPedidos />
    </ProtegerRuta>
  );
}

function ListaPedidos() {
  const [pedidos, setPedidos] = useState<Pedido[] | null>(null);
  const [filtro, setFiltro] = useState("");
  const [expandido, setExpandido] = useState<number | null>(null);
  const [error, setError] = useState<string | null>(null);
  const [accionando, setAccionando] = useState<number | null>(null);

  async function cargar() {
    setPedidos(null);
    const query = filtro ? `?estado=${filtro}` : "";
    const data = await apiFetch<Pedido[]>(`/pedidos${query}`);
    setPedidos(data);
  }

  useEffect(() => {
    cargar();
    // eslint-disable-next-line react-hooks/exhaustive-deps
  }, [filtro]);

  async function confirmar(pedido: Pedido) {
    setAccionando(pedido.id);
    setError(null);
    try {
      await apiFetch(`/pedidos/${pedido.id}/confirmar`, { method: "POST" });
      cargar();
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos confirmar el pedido.");
    } finally {
      setAccionando(null);
    }
  }

  async function cancelar(pedido: Pedido) {
    if (!confirm(`¿Cancelar el pedido #${pedido.id}?`)) return;
    setAccionando(pedido.id);
    setError(null);
    try {
      await apiFetch(`/pedidos/${pedido.id}/cancelar`, { method: "POST" });
      cargar();
    } catch (err) {
      setError(err instanceof ApiRequestError ? err.message : "No pudimos cancelar el pedido.");
    } finally {
      setAccionando(null);
    }
  }

  async function verDetalle(pedido: Pedido) {
    if (expandido === pedido.id) {
      setExpandido(null);
      return;
    }
    setExpandido(pedido.id);
    if (!pedido.detalles) {
      const completo = await apiFetch<Pedido>(`/pedidos/${pedido.id}`);
      setPedidos((actual) =>
        actual!.map((p) => (p.id === pedido.id ? completo : p)),
      );
    }
  }

  return (
    <div>
      <h1 className="text-2xl font-semibold">Pedidos</h1>

      <div className="mt-4 flex gap-2">
        {FILTROS.map((f) => (
          <button
            key={f.valor}
            onClick={() => setFiltro(f.valor)}
            className={`rounded-full px-3 py-1 text-sm ${
              filtro === f.valor
                ? "bg-neutral-900 text-white"
                : "bg-neutral-200 text-neutral-700"
            }`}
          >
            {f.texto}
          </button>
        ))}
      </div>

      {error && <p className="mt-4 text-red-600">{error}</p>}

      {!pedidos ? (
        <p className="mt-8 text-neutral-500">Cargando…</p>
      ) : pedidos.length === 0 ? (
        <p className="mt-8 text-neutral-500">No hay pedidos en este filtro.</p>
      ) : (
        <ul className="mt-6 space-y-3">
          {pedidos.map((pedido) => (
            <li key={pedido.id} className="rounded-lg border border-neutral-200 p-4">
              <div className="flex items-center justify-between">
                <div>
                  <button onClick={() => verDetalle(pedido)} className="text-left font-medium underline">
                    Pedido #{pedido.id}
                  </button>
                  <p className="text-sm text-neutral-600">
                    {pedido.cliente.nombre} · {pedido.cliente.email}
                  </p>
                </div>
                <div className="text-right">
                  <p className="font-semibold">{formatearPrecio(pedido.total)}</p>
                  <p className="text-sm text-neutral-600">
                    {ETIQUETAS_ESTADO[pedido.estado] ?? pedido.estado}
                  </p>
                </div>
              </div>

              {expandido === pedido.id && (
                <div className="mt-3 border-t border-neutral-100 pt-3 text-sm">
                  {!pedido.detalles ? (
                    <p className="text-neutral-500">Cargando detalle…</p>
                  ) : (
                    <ul className="space-y-1">
                      {pedido.detalles.map((d) => (
                        <li key={d.id} className="flex justify-between">
                          <span>
                            {d.cantidad} × {d.producto.nombre}
                          </span>
                          <span>{formatearPrecio(d.precio_unitario)} c/u</span>
                        </li>
                      ))}
                    </ul>
                  )}
                </div>
              )}

              {pedido.estado === "pendiente_pago" && (
                <div className="mt-3 flex gap-3">
                  <button
                    onClick={() => confirmar(pedido)}
                    disabled={accionando === pedido.id}
                    className="rounded-full bg-neutral-900 px-4 py-1.5 text-sm text-white disabled:opacity-50"
                  >
                    Confirmar pago
                  </button>
                  <button
                    onClick={() => cancelar(pedido)}
                    disabled={accionando === pedido.id}
                    className="rounded-full bg-neutral-200 px-4 py-1.5 text-sm text-neutral-700 disabled:opacity-50"
                  >
                    Cancelar
                  </button>
                </div>
              )}
              {pedido.estado === "confirmado" && (
                <div className="mt-3">
                  <button
                    onClick={() => cancelar(pedido)}
                    disabled={accionando === pedido.id}
                    className="rounded-full bg-neutral-200 px-4 py-1.5 text-sm text-neutral-700 disabled:opacity-50"
                  >
                    Cancelar (devuelve el stock)
                  </button>
                </div>
              )}
            </li>
          ))}
        </ul>
      )}
    </div>
  );
}
