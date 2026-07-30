export type Categoria = {
  id: number;
  nombre: string;
  slug: string;
};

export type Producto = {
  id: number;
  nombre: string;
  slug: string;
  descripcion: string | null;
  sku: string | null;
  precio: string;
  stock: number;
  activo: boolean;
  categoria: Categoria | null;
  categoria_id: number | null;
};

export type Cliente = {
  id: number;
  nombre: string;
  email: string;
  telefono: string | null;
};

export type DetallePedido = {
  id: number;
  producto_id: number;
  cantidad: number;
  precio_unitario: string;
  producto: Producto;
};

export type Pedido = {
  id: number;
  estado: string;
  canal_origen: string;
  total: string;
  created_at: string;
  cliente: Cliente;
  detalles?: DetallePedido[];
  detalles_count?: number;
};

export type Usuario = {
  id: number;
  nombre: string;
  email: string;
  tenant: { id: number; nombre: string };
};

export type ApiError = {
  error: {
    codigo: string;
    mensaje: string;
    detalles: Record<string, unknown>;
  };
};

export const ETIQUETAS_ESTADO: Record<string, string> = {
  pendiente_pago: "Pendiente de pago",
  pendiente_stock: "Pendiente de stock",
  confirmado: "Confirmado",
  despachado: "Despachado",
  entregado: "Entregado",
  cancelado: "Cancelado",
};
