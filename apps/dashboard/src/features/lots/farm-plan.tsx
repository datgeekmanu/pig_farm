import Link from "next/link"
import { formatNumber } from "@/lib/format"
import { cn } from "@/lib/utils"
import { lotsApi } from "./api"
import { TONE_TEXT } from "@/components/ui/badge"
import { lotHealth, PEN_CAPACITY } from "./growth"
import { LOT_STATUS } from "./lot-status"
import type { Lot } from "./types"

const PENS_PER_BUILDING = 8

/** Building-by-building map of pens; occupied pens link to their lot. */
export function FarmPlan({ lots }: { lots: Lot[] }) {
  return (
    <section className="flex min-w-0 flex-col gap-4 rounded-xl bg-muted p-5">
      <div className="flex flex-wrap items-center justify-between gap-3">
        <div>
          <h2 className="text-lg leading-6">Plan de l’élevage</h2>
          <p className="text-caption text-muted-foreground">Touchez une loge pour voir le lot</p>
        </div>
        <div className="flex flex-wrap gap-4 text-caption text-muted-foreground">
          <LegendItem swatch="border bg-card">Occupée</LegendItem>
          <LegendItem swatch="border border-dashed border-muted-foreground">Libre</LegendItem>
        </div>
      </div>

      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,360px),1fr))] gap-5">
        {lotsApi.buildings().map((building) => {
          const pens = Array.from({ length: PENS_PER_BUILDING }, (_, i) => `${building.id}-0${i + 1}`)
          const occupants = lots.filter((lot) => lot.pen.startsWith(`${building.id}-`))
          const animals = occupants.reduce((total, lot) => total + lot.headcount, 0)

          return (
            <div key={building.id} className="flex flex-col gap-2">
              <div className="flex flex-wrap items-baseline gap-2.5">
                <b className="font-heading text-body">{building.name}</b>
                <span className="text-caption text-muted-foreground">{building.description}</span>
                <span className="ml-auto text-caption text-muted-foreground tabular-nums">
                  {occupants.length} / {PENS_PER_BUILDING} loges · {formatNumber(animals)} animaux
                </span>
              </div>
              <div className="grid grid-cols-4 gap-2">
                {pens.map((pen) => (
                  <PenTile key={pen} pen={pen} lot={lots.find((lot) => lot.pen === pen)} />
                ))}
              </div>
            </div>
          )
        })}
      </div>
    </section>
  )
}

function PenTile({ pen, lot }: { pen: string; lot?: Lot }) {
  const tileClasses = "flex min-h-22 flex-col gap-0.5 rounded-lg px-3 py-2.5 text-left"

  if (!lot) {
    return (
      <div className={cn(tileClasses, "border border-dashed border-input text-muted-foreground")}>
        <span className="text-xs tabular-nums opacity-80">Loge {pen}</span>
        <span className="text-body font-bold">Libre</span>
        <span className="text-xs tabular-nums">Capacité {PEN_CAPACITY}</span>
      </div>
    )
  }

  const status = LOT_STATUS[lotHealth(lot)]
  return (
    <Link href={`/animals/${lot.id}`} className={cn(tileClasses, "border bg-card transition-colors hover:border-ring")}>
      <span className="text-xs tabular-nums opacity-80">Loge {pen}</span>
      <span className="text-body font-bold">{lot.id}</span>
      <span className="text-xs tabular-nums opacity-85">
        {formatNumber(lot.headcount)} · {formatNumber(lot.avgWeightKg, 1)} kg
      </span>
      <span className={cn("text-xs font-semibold", TONE_TEXT[status.tone])}>{status.label}</span>
    </Link>
  )
}

function LegendItem({ swatch, children }: { swatch: string; children: React.ReactNode }) {
  return (
    <span className="flex items-center gap-1.5">
      <span className={cn("size-3 rounded-[3px]", swatch)} />
      {children}
    </span>
  )
}
