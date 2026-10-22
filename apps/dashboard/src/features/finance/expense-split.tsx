import { Card } from "@/components/ui/card"
import { useActiveFarm } from "@/features/farms/active-farm"
import { formatMoneyCompact } from "@/lib/format"
import { EXPENSE_SPLIT } from "./api"

/** Share of total expenses per category; `total` in FCFA. */
export function ExpenseSplit({ total }: { total: number }) {
  const { farm } = useActiveFarm()

  return (
    <Card className="h-full">
      <h2 className="text-section">Répartition des dépenses</h2>
      <div className="flex h-3.5 gap-0.5 overflow-hidden rounded-full">
        {EXPENSE_SPLIT.map((category) => (
          <div key={category.name} style={{ width: `${category.share * 100}%`, background: category.color }} />
        ))}
      </div>
      <div className="flex flex-col">
        {EXPENSE_SPLIT.map((category) => (
          <div key={category.name} className="flex items-center gap-2.5 border-t py-2 text-sm">
            <span className="size-2.5 rounded-[2px]" style={{ background: category.color }} />
            <span className="flex-1">{category.name}</span>
            <span className="text-muted-foreground tabular-nums">{Math.round(category.share * 100)} %</span>
            <span className="min-w-28 text-right font-semibold tabular-nums">
              {formatMoneyCompact(total * category.share, farm.currency)}
            </span>
          </div>
        ))}
      </div>
    </Card>
  )
}
