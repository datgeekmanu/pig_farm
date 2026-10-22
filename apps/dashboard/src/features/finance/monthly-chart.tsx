"use client"

import { Bar, BarChart, CartesianGrid, ReferenceLine, XAxis } from "recharts"
import { Card } from "@/components/ui/card"
import { ChartContainer, ChartTooltip, ChartTooltipContent } from "@/components/ui/chart"
import { useActiveFarm } from "@/features/farms/active-farm"
import { formatMoneyCompact } from "@/lib/format"
import { BREAK_EVEN } from "./api"
import type { MonthlyFigures } from "./types"

const CONFIG = {
  revenue: { label: "Chiffre d’affaires", color: "var(--chart-1)" },
  expenses: { label: "Dépenses", color: "var(--chart-2)" },
}

/** Figures are in millions of FCFA. */
const toXof = (millions: number) => millions * 1_000_000

export function MonthlyChart({ months }: { months: MonthlyFigures[] }) {
  const { farm } = useActiveFarm()
  const money = (millions: number) => formatMoneyCompact(toXof(millions), farm.currency)

  return (
    <Card className="h-full">
      <div className="flex flex-wrap justify-between gap-3">
        <h2 className="text-section">Évolution mensuelle</h2>
        <div className="flex gap-3.5 text-caption text-muted-foreground">
          <Legend swatch="rounded-[2px] bg-chart-1">Chiffre d’affaires</Legend>
          <Legend swatch="rounded-[2px] bg-chart-2">Dépenses</Legend>
          <Legend swatch="h-0 w-3.5 border-t-2 border-dashed border-highlight">Seuil de rentabilité</Legend>
        </div>
      </div>
      <ChartContainer config={CONFIG} className="aspect-auto h-60 w-full">
        <BarChart data={months} barGap={4} margin={{ top: 12, right: 0, left: 0, bottom: 0 }}>
          <CartesianGrid vertical={false} />
          <XAxis dataKey="month" tickLine={false} axisLine={false} tickMargin={8} />
          <ChartTooltip
            content={
              <ChartTooltipContent formatter={(value, name) => `${CONFIG[name as keyof typeof CONFIG].label} · ${money(Number(value))}`} />
            }
          />
          <ReferenceLine
            y={BREAK_EVEN}
            stroke="var(--highlight)"
            strokeWidth={2}
            strokeDasharray="6 4"
            label={{
              value: `Seuil ${money(BREAK_EVEN)}`,
              position: "insideTopRight",
              fill: "var(--foreground)",
              fontSize: 12,
              fontWeight: 600,
            }}
          />
          <Bar dataKey="revenue" fill="var(--color-revenue)" radius={[4, 4, 0, 0]} maxBarSize={22} />
          <Bar dataKey="expenses" fill="var(--color-expenses)" radius={[4, 4, 0, 0]} maxBarSize={22} />
        </BarChart>
      </ChartContainer>
    </Card>
  )
}

function Legend({ swatch, children }: { swatch: string; children: React.ReactNode }) {
  return (
    <span className="flex items-center gap-1.5">
      <span className={`size-2.5 ${swatch}`} />
      {children}
    </span>
  )
}
