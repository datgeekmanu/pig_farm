import { Notice } from "@/components/shared/notice"
import { cn } from "@/lib/utils"
import type { Recap } from "../types"

export function RecapPanel({ recap }: { recap: Recap }) {
  return (
    <div className="flex flex-col gap-3.5">
      <dl className="overflow-hidden rounded-lg border bg-card">
        {recap.rows.map((row) => (
          <div key={row.label} className="flex justify-between gap-4 border-t px-4 py-3.5 text-body first:border-t-0">
            <dt className="text-muted-foreground">{row.label}</dt>
            <dd className={cn("text-right tabular-nums first-letter:uppercase", row.emphasis ? "font-bold" : "font-medium")}>
              {row.value}
            </dd>
          </div>
        ))}
      </dl>
      {recap.note && (
        <Notice tone="info" title="Ce qui va changer">
          {recap.note}
        </Notice>
      )}
      {recap.warning && (
        <Notice tone="warning" title="À vérifier">
          {recap.warning}
        </Notice>
      )}
    </div>
  )
}
