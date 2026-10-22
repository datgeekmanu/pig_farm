const RADIUS = 68;
const CIRCUMFERENCE = 2 * Math.PI * RADIUS;

export function GestationRing({ day, total }: { day: number; total: number }) {
  const progress = Math.min(Math.max(day / total, 0), 1);

  return (
    <div className="relative size-[132px] flex-none">
      <svg viewBox="0 0 160 160" className="size-full -rotate-90" aria-hidden>
        <circle cx="80" cy="80" r={RADIUS} fill="none" strokeWidth="12" className="stroke-white/20" />
        <circle
          cx="80"
          cy="80"
          r={RADIUS}
          fill="none"
          strokeWidth="12"
          strokeLinecap="round"
          strokeDasharray={CIRCUMFERENCE}
          strokeDashoffset={CIRCUMFERENCE * (1 - progress)}
          className="stroke-white"
        />
      </svg>
      <div className="absolute inset-0 flex flex-col items-center justify-center">
        <span className="font-heading text-4xl leading-none font-bold tabular-nums">{day}</span>
        <span className="text-xs font-semibold text-hero-muted">sur {total} jours</span>
      </div>
    </div>
  );
}
