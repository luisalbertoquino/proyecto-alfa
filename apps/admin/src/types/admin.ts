export type Categoria = {
  id: number;
  nombre: string;
  slug: string;
};

export type Necesidad = {
  id: number;
  nombre: string;
  slug: string;
};

export type ImagenProducto = {
  id: number;
  url: string;
  orden: number;
};

export type Producto = {
  id: number;
  nombre: string;
  slug: string;
  descripcion: string | null;
  sku: string | null;
  imagen_url: string | null;
  precio: string;
  stock: number;
  activo: boolean;
  destacado: boolean;
  categoria: Categoria | null;
  categoria_id: number | null;
  necesidades?: Necesidad[];
  imagenes?: ImagenProducto[];
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

export type Negocio = {
  nombre: string;
  quienes_somos: string | null;
  contacto_whatsapp: string | null;
  contacto_email: string | null;
  contacto_horario: string | null;
  color_primario: string | null;
  tipografia: "sans" | "serif" | null;
};

export type ProductoEnRutina = Producto & { pivot: { orden: number } };

export type Rutina = {
  id: number;
  nombre: string;
  slug: string;
  descripcion: string | null;
  productos: ProductoEnRutina[];
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
