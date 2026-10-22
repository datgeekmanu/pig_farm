"use client"

import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Spinner } from "@/components/ui/spinner"
import { PageHeader } from "@/components/shared/page-header"
import { TodayTasksList } from "@/features/alerts/today-tasks-list"
import { useMe } from "@/features/auth/queries"
import { useActiveFarm } from "@/features/farms/active-farm"
import { herdStats } from "@/features/lots/herd-stats"
import { useLots } from "@/features/lots/queries"
import { usePdfExport } from "@/hooks/use-pdf-export"
import { today } from "@/lib/dates"
import { formatDate } from "@/lib/format"
import { ActiveLotsList } from "./active-lots-list"
import { FieldPhotosCard } from "./field-photos-card"
import { HerdKpis } from "./herd-kpis"
import { HerdPerformanceCard } from "./herd-performance-card"

export function OverviewScreen() {
  const { farm } = useActiveFarm()
  const user = useMe().data
  const { data: lots } = useLots()
  const exportPdf = usePdfExport()

  // Avoid flashing "0 animaux · À surveiller" before the herd has loaded.
  if (!lots) return <Spinner className="mx-auto" />
  const stats = herdStats(lots)

  return (
    <>
      <PageHeader
        title={`Bonjour ${user?.firstName ?? ""}`}
        description={`${farm.name} · ${formatDate(today(), "full")}`}
        actions={
          <Button variant="outline" onClick={exportPdf}>
            <Icon name="file-arrow-down" />
            Rapport PDF
          </Button>
        }
      />
      <HerdKpis stats={stats} lotCount={lots.length} />
      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,420px),1fr))] gap-4">
        <HerdPerformanceCard currentGain={stats.dailyGain} />
        <FieldPhotosCard />
      </div>
      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,380px),1fr))] items-start gap-10">
        <ActiveLotsList lots={lots} />
        <TodayTasksList />
      </div>
    </>
  )
}
