"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useAuth } from "@/context/AuthContext";

export function Nav() {
  const { usuario, logout } = useAuth();
  const pathname = usePathname();

  if (!usuario) return null;

  const enlaces = [
    { href: "/pedidos", texto: "Pedidos" },
    { href: "/productos", texto: "Productos" },
  ];

  return (
    <header className="border-b border-neutral-200">
      <div className="mx-auto flex max-w-5xl items-center justify-between px-4 py-4">
        <div className="flex items-center gap-6">
          <span className="font-semibold">{usuario.tenant.nombre}</span>
          <nav className="flex gap-4 text-sm">
            {enlaces.map((enlace) => (
              <Link
                key={enlace.href}
                href={enlace.href}
                className={
                  pathname?.startsWith(enlace.href)
                    ? "font-medium text-neutral-900 underline"
                    : "text-neutral-600"
                }
              >
                {enlace.texto}
              </Link>
            ))}
          </nav>
        </div>
        <button onClick={() => logout()} className="text-sm text-neutral-600 underline">
          Cerrar sesión ({usuario.nombre})
        </button>
      </div>
    </header>
  );
}
