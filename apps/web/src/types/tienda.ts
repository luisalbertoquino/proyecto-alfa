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
  imagen_url: string | null;
  precio: string;
  stock: number;
  activo: boolean;
  destacado: boolean;
  categoria: Categoria | null;
  necesidades?: Necesidad[];
  imagenes?: ImagenProducto[];
};

export type ApiError = {
  error: {
    codigo: string;
    mensaje: string;
    detalles: Record<string, unknown>;
  };
};
