/**
 * Banner de marketing, sin lógica real detrás todavía: el checkout (Semana
 * 2) no calcula costo de envío ni aplica el umbral — ver
 * docs/architecture/apis.md, comparador de transportadoras es Fase 3 del
 * roadmap. Es intencionalmente solo texto por ahora, inspirado en la
 * investigación de competencia de Angie (chokchok.co, rosavainilla.co).
 */
export function BarraEnvioGratis() {
  return (
    <div className="bg-neutral-900 py-2 text-center text-xs text-white">
      Envíos gratis por compras superiores a $150.000
    </div>
  );
}
