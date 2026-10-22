import { cn } from "@/lib/utils"

export type KeyValue = { label: string; value: React.ReactNode; emphasis?: boolean }

/** Label left, value right, one row per line with top hairlines. */
export function KeyValueList({ items, className }: { items: KeyValue[]; className?: string }) {
  return (
    <dl className={cn("flex flex-col", className)}>
      {items.map((item) => (
        <div key={item.label} className="flex justify-between gap-4 border-t py-2 text-sm">
          <dt className="text-muted-foreground">{item.label}</dt>
          <dd className={cn("text-right tabular-nums", item.emphasis ? "font-bold" : "font-semibold")}>{item.value}</dd>
        </div>
      ))}
    </dl>
  )
}
