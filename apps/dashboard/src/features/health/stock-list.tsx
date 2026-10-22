"use client"

import Link from "next/link"
import { useState } from "react"
import { Badge } from "@/components/ui/badge"
import { IconTile } from "@/components/ui/card"
import { ProgressBar } from "@/components/shared/progress-bar"
import { SegmentedControl } from "@/components/shared/segmented-control"
import { entryHref } from "@/features/entry/links"
import { useActiveFarm } from "@/features/farms/active-farm"
import { formatMoney, formatNumber } from "@/lib/format"
import { stockLevel } from "./stock-level"
import type { StockCategory, StockItem } from "./types"

const CATEGORIES = [
  { value: "feed" as const, label: "Alimentation" },
  { value: "vet" as const, label: "Vétérinaire" },
]

const UNIT_SINGULAR: Record<string, string> = { doses: "dose", flacons: "flacon" }

export function StockList({ items }: { items: StockItem[] }) {
  const { farm } = useActiveFarm()
  const [category, setCategory] = useState<StockCategory>("feed")

  return (
    <section className="flex flex-col gap-3">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <h2 className="text-section">Stocks</h2>
        <SegmentedControl label="Catégorie" size="sm" options={CATEGORIES} value={category} onValueChange={setCategory} />
      </div>
      {items
        .filter((item) => item.category === category)
        .map((item) => {
          const level = stockLevel(item)
          // Scale so the threshold marker never sits at the far right edge.
          const scale = Math.max(item.quantity, item.alertThreshold) * 1.6
          return (
            <Link
              key={item.key}
              href={entryHref("stock-in", { item: item.key })}
              title="Saisir une entrée de stock"
              className="grid grid-cols-[44px_minmax(0,1fr)_120px] items-center gap-3.5 rounded-lg border bg-card px-4 py-3.5 transition-colors hover:border-ring"
            >
              <IconTile name={item.icon} className="size-11" />
              <div className="flex min-w-0 flex-col gap-1.5">
                <div className="flex justify-between gap-2">
                  <span className="text-body font-semibold">
                    {item.name} · {item.location}
                  </span>
                  <span className="text-sm font-bold tabular-nums">
                    {formatNumber(item.quantity)} {item.unit}
                  </span>
                </div>
                <ProgressBar
                  value={(item.quantity / scale) * 100}
                  markers={[(item.alertThreshold / scale) * 100]}
                  tone={level.isLow ? "danger" : "primary"}
                  label={`Niveau ${item.name}`}
                />
                <span className="text-xs text-muted-foreground">
                  Seuil d’alerte {formatNumber(item.alertThreshold)} {item.unit} · {formatMoney(item.unitPrice, farm.currency)} /{" "}
                  {UNIT_SINGULAR[item.unit] ?? item.unit}
                </span>
              </div>
              <div className="flex justify-end">
                <Badge tone={level.tone} size="sm">
                  {level.label}
                </Badge>
              </div>
            </Link>
          )
        })}
    </section>
  )
}
