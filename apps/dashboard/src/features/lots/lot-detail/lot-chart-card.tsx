"use client"

import { useState } from "react"
import { TrendChart, type TrendPoint } from "@/components/charts/trend-chart"
import { Badge } from "@/components/ui/badge"
import { Card } from "@/components/ui/card"
import { SelectField } from "@/components/shared/select-field"
import { formatNumber } from "@/lib/format"
import { gainBetween, GAIN_TARGET, ifipBandAt, lotHealth } from "../growth"
import { LOT_STATUS } from "../lot-status"
import type { Lot, Weighing } from "../types"

type Indicator = "weight" | "gain" | "uniformity"
type Reference = "ifip" | "farm"

const INDICATORS = [
  { value: "weight" as const, label: "Poids moyen" },
  { value: "gain" as const, label: "GMQ entre pesées" },
  { value: "uniformity" as const, label: "Homogénéité (CV)" },
]

// TODO(#2.4): "Moyenne de la ferme" needs the farm-wide weighing curve.
const REFERENCES = [
  { value: "ifip" as const, label: "Fuseau IFIP" },
  { value: "farm" as const, label: "Moyenne de la ferme" },
]

const CONFIG = { value: { label: "Lot", color: "var(--chart-1)" }, band: { label: "Référence", color: "var(--chart-2)" } }
const CV_CONFIG = { ...CONFIG, value: { label: "CV", color: "var(--chart-5)" } }

type LotChartCardProps = { lot: Lot; weighings: Weighing[]; selectedIndex: number }

export function LotChartCard({ lot, weighings, selectedIndex }: LotChartCardProps) {
  const [indicator, setIndicator] = useState<Indicator>("weight")
  const [reference, setReference] = useState<Reference>("ifip")
  const chart = buildChart(indicator, lot, weighings, selectedIndex)
  const status = LOT_STATUS[lotHealth(lot)]

  return (
    <Card size="sm" className="min-w-0 flex-[2_1_460px] gap-3.5">
      <div className="flex flex-wrap items-start justify-between gap-3">
        <div>
          <h2 className="text-section">{chart.title}</h2>
          <p className="text-caption text-muted-foreground">{chart.subtitle}</p>
        </div>
        <Badge tone={status.tone}>{status.curveNote}</Badge>
      </div>
      <div className="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-3">
        <SelectField label="Indicateur" options={INDICATORS} value={indicator} onValueChange={setIndicator} />
        <SelectField label="Référence" options={REFERENCES} value={reference} onValueChange={setReference} />
      </div>
      <TrendChart
        data={chart.data}
        config={indicator === "uniformity" ? CV_CONFIG : CONFIG}
        label={chart.title}
        yDomain={chart.domain}
        highlightIndex={chart.highlightIndex}
        callout={chart.callout}
        showBand={reference === "ifip"}
      />
    </Card>
  )
}

type ChartModel = {
  title: string
  subtitle: string
  data: TrendPoint[]
  domain: [number, number]
  highlightIndex: number
  callout: string
}

function buildChart(indicator: Indicator, lot: Lot, weighings: Weighing[], selected: number): ChartModel {
  const weekLabel = (w: Weighing) => `S${Math.round(w.ageWeeks)}`

  if (indicator === "gain") {
    const [min, max] = GAIN_TARGET[lot.phase]
    const data = weighings
      .slice(1)
      .map((w, i) => ({ label: weekLabel(w), value: gainBetween(weighings[i], w), band: [min, max] as [number, number] }))
    const top = Math.ceil(Math.max(max, ...data.map((p) => p.value)) / 100) * 100 + 100
    return {
      title: "GMQ entre pesées",
      subtitle: `Cible ${lot.phase.toLowerCase()} : ${min}–${max} g/j`,
      data,
      domain: [0, top],
      highlightIndex: selected - 1,
      callout: `${formatNumber(data.at(-1)?.value ?? 0)} g/j`,
    }
  }

  if (indicator === "uniformity") {
    return {
      title: "Homogénéité du lot (CV %)",
      subtitle: "Objectif : CV inférieur à 10 %",
      data: weighings.map((w) => ({ label: weekLabel(w), value: w.uniformityCv, band: [0, 10] })),
      domain: [0, 25],
      highlightIndex: selected,
      callout: `${formatNumber(lot.uniformityCv, 1)} %`,
    }
  }

  const data = weighings.map((w) => ({ label: weekLabel(w), value: w.avgWeightKg, band: ifipBandAt(w.ageWeeks) }))
  const values = data.flatMap((p) => [p.value, ...p.band])
  return {
    title: "Courbe de croissance",
    subtitle: "Poids moyen mesuré face au fuseau de référence IFIP (âge en semaines)",
    data,
    domain: [Math.max(0, Math.floor((Math.min(...values) - 5) / 20) * 20), Math.ceil((Math.max(...values) + 10) / 20) * 20],
    highlightIndex: selected,
    callout: `${formatNumber(weighings[selected].avgWeightKg, 1)} kg`,
  }
}
