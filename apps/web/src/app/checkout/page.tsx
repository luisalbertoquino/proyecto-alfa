"use client";

import { useState } from "react";
import Link from "next/link";
import { useCarrito } from "@/context/CarritoContext";
import { apiFetch, ApiRequestError } from "@/lib/api";
import { formatearPrecio } from "@/lib/formato";

type Pedido = {
  id: number;
  estado: string;
  total: string;
};

export default function CheckoutPage() {
  const { items, total, vaciar } = useCarrito();
  const [nombre, setNombre] = useState("");
  const [email, setEmail] = useState("");
  const [telefono, setTelefono] = useState("");
  const [enviando, setEnviando] = useState(false);
  const [error, setError] = useState<string | null>(null);
  const [pedido, setPedido] = useState<Pedido | null>(null);

  if (pedido) {
    return (
      <div className="max-w-md">
        <h1 className="text-2xl font-semibold">¡Pedido recibido!</h1>
        <p className="mt-4 text-neutral-700">
          Tu pedido <strong>#{pedido.id}</strong> por{" "}
          <strong>{formatearPrecio(pedido.total)}</strong> quedó registrado
          como <em>pendiente de confirmación de pago</em>. Te vamos a
          contactar por email o teléfono para confirmar el pago y coordinar
          el envío.
        </p>
        <Link href="/" className="mt-6 inline-block underline">
          Seguir comprando
        </Link>
      </div>
    );
  }

  if (items.length === 0) {
    return (
      <div>
        <h1 className="text-2xl font-semibold">Tu carrito está vacío</h1>
        <Link href="/" className="mt-4 inline-block underline">
          Ver productos
        </Link>
      </div>
    );
  }

  async function enviarPedido(e: React.FormEvent) {
    e.preventDefault();
    setEnviando(true);
    setError(null);

    try {
      const nuevoPedido = await apiFetch<Pedido>("/tienda/pedidos", {
        method: "POST",
        body: JSON.stringify({
          cliente: { nombre, email, telefono: telefono || null },
          items: items.map((i) => ({
            producto_id: i.productoId,
            cantidad: i.cantidad,
          })),
        }),
      });
      setPedido(nuevoPedido);
      vaciar();
    } catch (err) {
      if (err instanceof ApiRequestError) {
        setError(err.message);
      } else {
        setError("No pudimos registrar tu pedido. Intenta de nuevo.");
      }
    } finally {
      setEnviando(false);
    }
  }

  return (
    <div className="max-w-md">
      <h1 className="text-2xl font-semibold">Confirmar pedido</h1>
      <p className="mt-2 text-neutral-600">
        Este es un prototipo: el pago se confirma manualmente por nuestro
        equipo después de recibir tu pedido, no hay pasarela de pago todavía.
      </p>

      <form onSubmit={enviarPedido} className="mt-6 space-y-4">
        <div>
          <label className="block text-sm font-medium">Nombre</label>
          <input
            required
            value={nombre}
            onChange={(e) => setNombre(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>
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
          <label className="block text-sm font-medium">
            Teléfono (opcional)
          </label>
          <input
            value={telefono}
            onChange={(e) => setTelefono(e.target.value)}
            className="mt-1 w-full rounded border border-neutral-300 px-3 py-2"
          />
        </div>

        <div className="flex items-center justify-between border-t border-neutral-200 pt-4">
          <p className="font-semibold">Total</p>
          <p className="font-semibold">{formatearPrecio(total)}</p>
        </div>

        {error && <p className="text-sm text-red-600">{error}</p>}

        <button
          type="submit"
          disabled={enviando}
          className="w-full rounded-full bg-neutral-900 px-5 py-3 text-white disabled:opacity-50"
        >
          {enviando ? "Enviando..." : "Confirmar pedido"}
        </button>
      </form>
    </div>
  );
}
