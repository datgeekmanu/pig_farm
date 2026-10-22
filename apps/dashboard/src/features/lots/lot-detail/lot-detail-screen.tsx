"use client"

import Link from "next/link"
import { useParams } from "next/navigation"
import { useState } from "react"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { EmptyState } from "@/components/shared/empty-state"
import { KeyValueList } from "@/components/shared/key-value-list"
import { Spinner } from "@/components/ui/spinner"
import { formatDate, formatNumber } from "@/lib/format"
import { buildingOf, gainBetween, weighingHistory } from "../growth"
import { useLot, useLotEvents } from "../queries"
import { LotChartCard } from "./lot-chart-card"
import { LotHistory } from "./lot-history"
import { LotSummaryCard } from "./lot-summary-card"
import { WeighingStrip } from "./weighing-strip"

export function LotDetailScreen() {
  const { lotId } = useParams<{ lotId: string }>()
  const { data: lot, isPending } = useLot(lotId)
  const events = useLotEvents(lotId).data ?? []
  const [selected, setSelected] = useState<number | null>(null)

  if (isPending) return <Spinner className="mx-auto" />
  if (!lot) return <EmptyState icon="layer-group">Ce lot n’existe pas dans la ferme active.</EmptyState>

  const weighings = weighingHistory(lot)
  const selectedIndex = selected ?? weighings.length - 1
  const weighing = weighings[selectedIndex]
  const previous = weighings[selectedIndex - 1]

  return (
    <div className="flex flex-col gap-5">
      <div className="flex flex-wrap items-center gap-3">
        <Button variant="outline" asChild>
          <Link href="/animals">
            <Icon name="arrow-left" />
            Animaux
          </Link>
        </Button>
        <span className="text-sm text-muted-foreground">
          {buildingOf(lot)} · Loge {lot.pen}
        </span>
      </div>

      <div className="flex flex-wrap items-stretch gap-4">
        <LotSummaryCard lot={lot} />
        <LotChartCard lot={lot} weighings={weighings} selectedIndex={selectedIndex} />
      </div>

      <WeighingStrip lotId={lot.id} weighings={weighings} selectedIndex={selectedIndex} onSelect={setSelected} />

      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,380px),1fr))] items-start gap-4">
        <Card size="sm" className="gap-2">
          <span className="text-caption text-muted-foreground">Pesée sélectionnée</span>
          <b className="text-base">{formatDate(weighing.date, "long")}</b>
          <KeyValueList
            items={[
              { label: "Poids moyen", value: `${formatNumber(weighing.avgWeightKg, 1)} kg` },
              { label: "GMQ depuis la précédente", value: previous ? `${formatNumber(gainBetween(previous, weighing))} g/j` : "—" },
              { label: "Homogénéité (CV)", value: `${formatNumber(weighing.uniformityCv, 1)} %` },
            ]}
          />
        </Card>
        <LotHistory lot={lot} events={events} lastWeighing={weighings[weighings.length - 1]} />
      </div>
    </div>
  )
}
