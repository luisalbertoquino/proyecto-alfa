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
  imagen_url: string | null;
  precio: string;
  stock: number;
  activo: boolean;
  categoria: Categoria | null;
};

export type ApiError = {
  error: {
    codigo: string;
    mensaje: string;
    detalles: Record<string, unknown>;
  };
};
