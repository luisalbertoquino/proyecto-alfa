"use client";

import {
  createContext,
  useContext,
  useEffect,
  useState,
  type ReactNode,
} from "react";
import type { Producto } from "@/types/tienda";

export type ItemCarrito = {
  productoId: number;
  nombre: string;
  slug: string;
  precio: string;
  stock: number;
  cantidad: number;
};

type CarritoContextValor = {
  items: ItemCarrito[];
  agregar: (producto: Producto, cantidad: number) => void;
  actualizarCantidad: (productoId: number, cantidad: number) => void;
  quitar: (productoId: number) => void;
  vaciar: () => void;
  total: number;
  cantidadTotal: number;
};

const CarritoContext = createContext<CarritoContextValor | null>(null);

const CLAVE_LOCALSTORAGE = "proyecto-alfa-carrito";

export function CarritoProvider({ children }: { children: ReactNode }) {
  const [items, setItems] = useState<ItemCarrito[]>([]);
  const [cargado, setCargado] = useState(false);

  // Carga inicial desde localStorage (solo en el navegador).
  useEffect(() => {
    const guardado = window.localStorage.getItem(CLAVE_LOCALSTORAGE);
    if (guardado) {
      try {
        setItems(JSON.parse(guardado));
      } catch {
        // localStorage corrupto: se ignora y empieza con carrito vacío.
      }
    }
    setCargado(true);
  }, []);

  // Persiste cada cambio, una vez que ya se hizo la carga inicial (para no
  // sobrescribir lo guardado con un carrito vacío antes de leerlo).
  useEffect(() => {
    if (cargado) {
      window.localStorage.setItem(CLAVE_LOCALSTORAGE, JSON.stringify(items));
    }
  }, [items, cargado]);

  function agregar(producto: Producto, cantidad: number) {
    setItems((actual) => {
      const existente = actual.find((i) => i.productoId === producto.id);
      if (existente) {
        return actual.map((i) =>
          i.productoId === producto.id
            ? { ...i, cantidad: Math.min(i.cantidad + cantidad, producto.stock) }
            : i,
        );
      }
      return [
        ...actual,
        {
          productoId: producto.id,
          nombre: producto.nombre,
          slug: producto.slug,
          precio: producto.precio,
          stock: producto.stock,
          cantidad: Math.min(cantidad, producto.stock),
        },
      ];
    });
  }

  function actualizarCantidad(productoId: number, cantidad: number) {
    setItems((actual) =>
      actual.map((i) =>
        i.productoId === productoId
          ? { ...i, cantidad: Math.max(1, Math.min(cantidad, i.stock)) }
          : i,
      ),
    );
  }

  function quitar(productoId: number) {
    setItems((actual) => actual.filter((i) => i.productoId !== productoId));
  }

  function vaciar() {
    setItems([]);
  }

  const total = items.reduce(
    (suma, i) => suma + Number(i.precio) * i.cantidad,
    0,
  );
  const cantidadTotal = items.reduce((suma, i) => suma + i.cantidad, 0);

  return (
    <CarritoContext.Provider
      value={{ items, agregar, actualizarCantidad, quitar, vaciar, total, cantidadTotal }}
    >
      {children}
    </CarritoContext.Provider>
  );
}

export function useCarrito() {
  const contexto = useContext(CarritoContext);
  if (!contexto) {
    throw new Error("useCarrito debe usarse dentro de un CarritoProvider");
  }
  return contexto;
}
