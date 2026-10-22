"use client"

import Link from "next/link"
import { Icon } from "@/components/icon"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { HeroCard } from "@/components/shared/hero-card"
import { PageHeader } from "@/components/shared/page-header"
import { StatCard } from "@/components/shared/stat-card"
import { entryHref } from "@/features/entry/links"
import { formatDate } from "@/lib/format"
import { FarrowingCalculator } from "./farrowing-calculator"
import { GESTATION_DAYS, gestationOf, isGestating } from "./gestation"
import { GestationList } from "./gestation-list"
import { useSows } from "./queries"
import { SowsToServe } from "./sows-to-serve"

// TODO(#3.6): GTTT herd KPIs from the API.
const HERD_KPIS = [
  { label: "Taux de mise bas", value: "86 %", tone: "success" as const, note: "Objectif 85 %" },
  { label: "Sevrés / truie / an", value: "24,1", tone: "success" as const, note: "+0,8 sur 12 mois" },
  { label: "Mortalité sous mère", value: "9,8 %", tone: "warning" as const, note: "Objectif < 9 %" },
]

export function ReproductionScreen() {
  const sows = useSows().data ?? []
  const gestations = sows.filter(isGestating).map(gestationOf)
  const dueSoon = gestations.filter((g) => g.isDueSoon)

  return (
    <div className="flex flex-col gap-6">
      <PageHeader
        title="Reproduction"
        description={`48 truies · 6 verrats · gestation de ${GESTATION_DAYS} jours`}
        actions={
          <Button asChild>
            <Link href={entryHref("service")}>
              <Icon name="plus" />
              Saisir une saillie
            </Link>
          </Button>
        }
      />

      <div className="grid grid-cols-[repeat(auto-fit,minmax(200px,1fr))] gap-4">
        <HeroCard
          label="Mises bas · 7 prochains jours"
          value={dueSoon.length}
          caption={
            dueSoon.length ? dueSoon.map((g) => `${g.sow.id} · ${formatDate(g.dueDate)}`).join(" · ") : "Aucune mise bas cette semaine"
          }
        />
        {HERD_KPIS.map((kpi) => (
          <StatCard
            key={kpi.label}
            size="md"
            label={kpi.label}
            value={kpi.value}
            footer={
              <Badge tone={kpi.tone} size="sm">
                {kpi.note}
              </Badge>
            }
          />
        ))}
      </div>

      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,380px),1fr))] items-start gap-6">
        <GestationList gestations={gestations} />
        <div className="flex flex-col gap-4">
          <FarrowingCalculator />
          <SowsToServe sows={sows.filter((sow) => sow.status === "empty")} />
        </div>
      </div>
    </div>
  )
}
