import Image from "next/image"
import Link from "next/link"
import { Icon } from "@/components/icon"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card } from "@/components/ui/card"
import { Pill } from "@/components/shared/pill"
import { StatGrid } from "@/components/shared/stat-grid"
import { useIsOwner } from "@/features/auth/queries"
import { entryHref } from "@/features/entry/links"
import type { FlowKey } from "@/features/entry/types"
import { fromIsoDate } from "@/lib/dates"
import { formatDate, formatNumber } from "@/lib/format"
import { buildingOf, lotHealth, PEN_CAPACITY } from "../growth"
import type { Lot } from "../types"
import { LOT_STATUS } from "../lot-status"

const ACTIONS: { flow: FlowKey; label: string; icon: string; ownerOnly?: boolean }[] = [
  { flow: "mortality", label: "Mortalité", icon: "skull-crossbones" },
  { flow: "treatment", label: "Traitement", icon: "syringe" },
  { flow: "sale", label: "Vente", icon: "sack-dollar", ownerOnly: true },
]

export function LotSummaryCard({ lot }: { lot: Lot }) {
  const isOwner = useIsOwner()
  const status = LOT_STATUS[lotHealth(lot)]
  const location = `${buildingOf(lot)} · Loge ${lot.pen}`

  return (
    <Card size="flush" className="max-w-full flex-[1_1_340px]">
      <div className="relative h-42.5 bg-sidebar">
        <Image
          src="/images/selective-closeup-shot-pink-pigs-barn.jpg"
          alt="Porcs du lot à l’auge"
          fill
          sizes="400px"
          className="object-cover"
        />
        <span className="absolute bottom-3.5 left-3.5">
          <Pill tone="brand">{location}</Pill>
        </span>
      </div>

      <div className="flex flex-col gap-4 p-5">
        <div className="flex items-start justify-between gap-2">
          <div>
            <h1 className="text-2xl font-bold">{lot.id}</h1>
            <p className="text-caption text-muted-foreground">
              {lot.phase} · mis en lot le {formatDate(fromIsoDate(lot.startDate), "dayMonth")}
            </p>
          </div>
          <Badge tone={status.tone}>{status.label}</Badge>
        </div>

        <StatGrid
          stats={[
            { label: "Effectif", value: `${formatNumber(lot.headcount)} / ${formatNumber(lot.initialHeadcount)}` },
            { label: "Poids moyen", value: `${formatNumber(lot.avgWeightKg, 1)} kg` },
            { label: "Croissance (GMQ)", value: lot.dailyGain ? `${formatNumber(lot.dailyGain)} g/j` : "—" },
            { label: "Indice de conso. (IC)", value: lot.feedConversion ? formatNumber(lot.feedConversion, 2) : "—" },
            { label: "Homogénéité (CV)", value: `${formatNumber(lot.uniformityCv, 1)} %` },
            { label: "Densité loge", value: `${formatNumber((lot.headcount / PEN_CAPACITY) * 100)} %` },
          ]}
        />

        <div className="flex flex-col gap-2">
          <Button size="lg" asChild>
            <Link href={entryHref("weighing", { lot: lot.id })}>
              <Icon name="weight-scale" />
              Saisir une pesée
            </Link>
          </Button>
          <div className="grid grid-cols-[repeat(auto-fit,minmax(110px,1fr))] gap-2">
            {ACTIONS.filter((action) => isOwner || !action.ownerOnly).map((action) => (
              <Button key={action.flow} variant="outline" asChild>
                <Link href={entryHref(action.flow, { lot: lot.id })}>
                  <Icon name={action.icon} />
                  {action.label}
                </Link>
              </Button>
            ))}
          </div>
        </div>
      </div>
    </Card>
  )
}
