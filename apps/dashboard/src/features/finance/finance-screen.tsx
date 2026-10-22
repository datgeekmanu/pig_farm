"use client"

import Link from "next/link"
import { useState } from "react"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { EmptyState } from "@/components/shared/empty-state"
import { HeroCard } from "@/components/shared/hero-card"
import { PageHeader } from "@/components/shared/page-header"
import { SectionHeader } from "@/components/shared/section-header"
import { SegmentedControl } from "@/components/shared/segmented-control"
import { StatCard } from "@/components/shared/stat-card"
import { useIsOwner } from "@/features/auth/queries"
import { entryHref } from "@/features/entry/links"
import { useActiveFarm } from "@/features/farms/active-farm"
import { usePdfExport } from "@/hooks/use-pdf-export"
import { currencyLabel, formatMoneyCompact, formatNumber } from "@/lib/format"
import { BREAK_EVEN, financeApi, PIGS_SOLD } from "./api"
import { ExpenseSplit } from "./expense-split"
import { MonthlyChart } from "./monthly-chart"
import { useSales } from "./queries"
import { SalesTable } from "./sales-table"

const PERIODS = [
  { value: "3" as const, label: "3 mois" },
  { value: "6" as const, label: "6 mois" },
  { value: "12" as const, label: "12 mois" },
]

type Period = (typeof PERIODS)[number]["value"]

const MILLION = 1_000_000

export function FinanceScreen() {
  const isOwner = useIsOwner()
  const { farm } = useActiveFarm()
  const [period, setPeriod] = useState<Period>("6")
  const sales = useSales().data ?? []
  const exportPdf = usePdfExport()

  if (!isOwner) return <EmptyState icon="lock">Les finances sont réservées au propriétaire de la ferme.</EmptyState>

  const months = financeApi.monthly(Number(period))
  const revenue = months.reduce((total, m) => total + m.revenue, 0) * MILLION
  const expenses = months.reduce((total, m) => total + m.expenses, 0) * MILLION
  const money = (xof: number) => formatMoneyCompact(xof, farm.currency)

  return (
    <div className="flex flex-col gap-6">
      <PageHeader
        title="Argent"
        description={`Montants en ${currencyLabel(farm.currency)} · ${period} derniers mois`}
        actions={
          <>
            <SegmentedControl label="Période" options={PERIODS} value={period} onValueChange={setPeriod} />
            <Button variant="outline" onClick={exportPdf}>
              <Icon name="file-pdf" />
              Rapport PDF
            </Button>
            <Button variant="outline" asChild>
              <Link href={entryHref("expense")}>
                <Icon name="minus" />
                Saisir une dépense
              </Link>
            </Button>
            <Button asChild>
              <Link href={entryHref("sale")}>
                <Icon name="plus" />
                Saisir une vente
              </Link>
            </Button>
          </>
        }
      />

      <div className="grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4">
        <HeroCard
          size="md"
          label="Bénéfice brut"
          value={money(revenue - expenses)}
          change={`${formatNumber(((revenue - expenses) / revenue) * 100, 1)} %`}
          caption="Marge sur chiffre d’affaires"
        />
        <StatCard
          size="md"
          label="Chiffre d’affaires"
          value={money(revenue)}
          footer={<Note>{formatNumber(PIGS_SOLD[Number(period)])} porcs vendus</Note>}
        />
        <StatCard size="md" label="Dépenses" value={money(expenses)} footer={<Note>Dont alimentation 62 %</Note>} />
        <StatCard
          size="md"
          label="Seuil de rentabilité"
          value={`${money(BREAK_EVEN * MILLION)}/mois`}
          footer={
            <Note>
              {months.filter((m) => m.revenue >= BREAK_EVEN).length} mois sur {period} au-dessus
            </Note>
          }
        />
      </div>

      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,380px),1fr))] items-stretch gap-4">
        <MonthlyChart months={months} />
        <ExpenseSplit total={expenses} />
      </div>

      <section className="flex flex-col gap-3">
        {/* TODO(#6.1): sales list screen */}
        <SectionHeader title="Ventes récentes" aside={<span className="text-body text-foreground">Voir tout</span>} />
        <SalesTable sales={sales} />
      </section>
    </div>
  )
}

function Note({ children }: { children: React.ReactNode }) {
  return <span className="text-caption text-muted-foreground">{children}</span>
}
