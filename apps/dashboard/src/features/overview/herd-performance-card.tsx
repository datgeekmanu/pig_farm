"use client"

import { useState } from "react"
import { TrendChart, type TrendPoint } from "@/components/charts/trend-chart"
import { Card } from "@/components/ui/card"
import { formatNumber } from "@/lib/format"
import { cn } from "@/lib/utils"

type Series = "gain" | "band" | "deaths"

const SERIES: { key: Series; label: string; swatch: string }[] = [
  { key: "gain", label: "GMQ (g/j)", swatch: "rounded-full bg-chart-1" },
  { key: "band", label: "Cible IFIP", swatch: "rounded-[2px] bg-chart-2/40" },
  { key: "deaths", label: "Morts / sem.", swatch: "rounded-[2px] bg-chart-3" },
]

const CHART_CONFIG = {
  value: { label: "GMQ (g/j)", color: "var(--chart-1)" },
  band: { label: "Cible IFIP", color: "var(--chart-2)" },
  bar: { label: "Morts", color: "var(--chart-3)" },
}

// TODO(#2.4): weekly herd series from the API; last point is today's weighted GMQ.
const WEEKS = [35, 36, 37, 38, 39, 40, 41, 42]
const PAST_GAIN = [612, 628, 619, 641, 655, 648, 662]
const DEATHS = [3, 1, 2, 0, 2, 1, 0, 1]

export function HerdPerformanceCard({ currentGain }: { currentGain: number }) {
  const [visible, setVisible] = useState<Record<Series, boolean>>({ gain: true, band: true, deaths: true })
  const toggle = (key: Series) => setVisible((prev) => ({ ...prev, [key]: !prev[key] }))

  const data: TrendPoint[] = WEEKS.map((week, i) => ({
    label: `S${week}`,
    value: PAST_GAIN[i] ?? currentGain,
    band: [600, 700],
    bar: DEATHS[i],
  }))

  return (
    <Card className="h-full gap-3.5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-section">Performances du cheptel</h2>
          <p className="text-caption text-muted-foreground">GMQ moyen pondéré et mortalité, 8 dernières semaines</p>
        </div>
        <div className="flex flex-wrap gap-1.5">
          {SERIES.map((series) => (
            <button
              key={series.key}
              type="button"
              aria-pressed={visible[series.key]}
              onClick={() => toggle(series.key)}
              className={cn(
                "flex h-8 cursor-pointer items-center gap-2 rounded-full border bg-card px-3 text-caption font-semibold transition-[background-color,opacity] hover:bg-muted",
                !visible[series.key] && "opacity-50",
              )}
            >
              <span className={cn("size-2.5", series.swatch)} />
              {series.label}
            </button>
          ))}
        </div>
      </div>
      <TrendChart
        data={data}
        config={CHART_CONFIG}
        label="GMQ et mortalité par semaine"
        yDomain={[500, 760]}
        callout={`${formatNumber(currentGain)} g/j`}
        showLine={visible.gain}
        showBand={visible.band}
        showBars={visible.deaths}
      />
    </Card>
  )
}
