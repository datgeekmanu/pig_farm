import { cn } from "@/lib/utils"

export type Stat = { label: string; value: React.ReactNode }

/** Hairline-separated grid of small label/value cells. */
export function StatGrid({ stats, className }: { stats: Stat[]; className?: string }) {
  return (
    <dl className={cn("grid grid-cols-2 gap-px overflow-hidden rounded-lg border bg-border", className)}>
      {stats.map((stat) => (
        <div key={stat.label} className="flex flex-col bg-card px-3 py-2.5">
          <dt className="text-xs text-muted-foreground">{stat.label}</dt>
          <dd className="font-bold tabular-nums">{stat.value}</dd>
        </div>
      ))}
    </dl>
  )
}
