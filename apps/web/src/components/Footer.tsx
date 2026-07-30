import Link from "next/link";

export function Footer() {
  return (
    <footer className="border-t border-neutral-200 py-8">
      <div className="mx-auto flex max-w-5xl flex-col gap-4 px-4 text-sm text-neutral-600 sm:flex-row sm:justify-between">
        <p>Skincare Piloto — prototipo en construcción.</p>
        <nav className="flex gap-4">
          <Link href="/quienes-somos" className="hover:text-neutral-900">
            Quiénes somos
          </Link>
          <Link href="/contactanos" className="hover:text-neutral-900">
            Contáctanos
          </Link>
        </nav>
      </div>
    </footer>
  );
}
