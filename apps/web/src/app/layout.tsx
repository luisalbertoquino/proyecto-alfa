import type { Metadata } from "next";
import { Geist, Geist_Mono } from "next/font/google";
import "./globals.css";
import { apiFetch } from "@/lib/api";
import type { Negocio } from "@/types/tienda";
import { CarritoProvider } from "@/context/CarritoContext";
import { Header } from "@/components/Header";
import { BarraEnvioGratis } from "@/components/BarraEnvioGratis";
import { Footer } from "@/components/Footer";

const geistSans = Geist({
  variable: "--font-geist-sans",
  subsets: ["latin"],
});

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
});

// El layout raíz lee el tenant (nombre, color, tipografía) en cada
// petición — sin esto, Next.js cachearía la primera respuesta en el build
// y todas las páginas (incluidas las que ya son "estáticas", como
// /carrito) quedarían con el tema congelado del momento del build, sin
// reflejar un cambio de color/nombre hecho después desde el panel. Mismo
// motivo que force-dynamic en las páginas de catálogo — ver
// docs/estado-actual.md.
export const dynamic = "force-dynamic";

export async function generateMetadata(): Promise<Metadata> {
  const negocio = await apiFetch<Negocio>("/tienda/negocio");

  return {
    title: negocio.nombre,
    description: `Tienda de ${negocio.nombre} — Proyecto Alfa`,
  };
}

export default async function RootLayout({
  children,
}: Readonly<{
  children: React.ReactNode;
}>) {
  const negocio = await apiFetch<Negocio>("/tienda/negocio");
  const colorPrimario = negocio.color_primario ?? "#171717";
  const claseTipografia = negocio.tipografia === "serif" ? "font-serif-tema" : "";

  return (
    <html
      lang="es"
      className={`${geistSans.variable} ${geistMono.variable} ${claseTipografia} h-full antialiased`}
      style={{ "--color-brand-primario": colorPrimario } as React.CSSProperties}
    >
      <body className="min-h-full flex flex-col">
        <CarritoProvider>
          <BarraEnvioGratis />
          <Header nombre={negocio.nombre} />
          <main className="mx-auto w-full max-w-5xl flex-1 px-4 py-8">
            {children}
          </main>
          <Footer nombre={negocio.nombre} />
        </CarritoProvider>
      </body>
    </html>
  );
}
