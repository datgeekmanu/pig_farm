import { ifipBand } from "@/lib/ifip";

const WIDTH = 300;
const HEIGHT = 120;
const PAD_Y = 10;

// Actual lot averages over the IFIP reference band (#2.4).
export function GrowthChart({ points, weeks }: { points: { week: number; kg: number }[]; weeks: number }) {
  const band = Array.from({ length: weeks + 1 }, (_, week) => ({ week, ...ifipBand(week) }));
  const maxKg = Math.max(...band.map((b) => b.high), ...points.map((p) => p.kg));
  const x = (week: number) => (week / weeks) * WIDTH;
  const y = (kg: number) => HEIGHT - PAD_Y - (kg / maxKg) * (HEIGHT - 2 * PAD_Y);

  const bandPoints = [
    ...band.map((b) => `${x(b.week)},${y(b.low)}`),
    ...[...band].reverse().map((b) => `${x(b.week)},${y(b.high)}`),
  ].join(" ");
  const line = points.map((p) => `${x(p.week)},${y(p.kg)}`).join(" ");
  const last = points.at(-1);

  return (
    <figure className="flex flex-col gap-2.5">
      <svg viewBox={`0 0 ${WIDTH} ${HEIGHT}`} className="block h-auto w-full" role="img" aria-label="Courbe de croissance du lot comparée à la référence IFIP">
        <line x1="0" y1={HEIGHT - PAD_Y} x2={WIDTH} y2={HEIGHT - PAD_Y} className="stroke-border" />
        <line x1="0" y1={HEIGHT / 2} x2={WIDTH} y2={HEIGHT / 2} strokeDasharray="3 4" className="stroke-border" />
        <line x1="0" y1={PAD_Y} x2={WIDTH} y2={PAD_Y} strokeDasharray="3 4" className="stroke-border" />
        <polygon points={bandPoints} className="fill-muted" />
        <polyline
          points={line}
          fill="none"
          strokeWidth="2.5"
          strokeLinejoin="round"
          strokeLinecap="round"
          className="stroke-chart-1"
        />
        {last && <circle cx={x(last.week)} cy={y(last.kg)} r="5" strokeWidth="2" className="fill-switch-on stroke-card" />}
      </svg>
      <figcaption className="flex gap-4 text-xs text-muted-foreground">
        <span className="flex items-center gap-1.5">
          <span className="h-[3px] w-3 rounded-sm bg-chart-1" />
          Pesées réelles
        </span>
        <span className="flex items-center gap-1.5">
          <span className="h-2 w-3 rounded-sm bg-muted" />
          Référence IFIP
        </span>
      </figcaption>
    </figure>
  );
}
