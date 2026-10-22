import Link from "next/link"
import { InitialsAvatar, LOT_COLORS } from "@/components/shared/initials-avatar"
import { ProgressBar } from "@/components/shared/progress-bar"
import { SectionHeader } from "@/components/shared/section-header"
import { buildingOf, GAIN_TARGET, lotHealth } from "@/features/lots/growth"
import type { Lot } from "@/features/lots/types"
import { formatNumber } from "@/lib/format"
import { cn } from "@/lib/utils"

const GAIN_TONE = { new: "text-muted-foreground", good: "text-success", watch: "text-danger" } as const

export function ActiveLotsList({ lots }: { lots: Lot[] }) {
  return (
    <section className="flex flex-col">
      <SectionHeader
        className="mb-2"
        title={
          <>
            Lots en cours <span className="text-base font-medium text-muted-foreground">(GMQ vs cible)</span>
          </>
        }
        aside={
          <Link href="/animals" className="text-body text-foreground">
            Voir tout
          </Link>
        }
      />
      {lots.map((lot, i) => {
        const [min, max] = GAIN_TARGET[lot.phase]
        return (
          <Link
            key={lot.id}
            href={`/animals/${lot.id}`}
            className="grid grid-cols-[48px_minmax(0,1.3fr)_minmax(0,1fr)_84px] items-center gap-4 rounded-md border-t px-2 py-3.5 transition-colors hover:bg-hover-overlay"
          >
            <InitialsAvatar color={LOT_COLORS[i % LOT_COLORS.length]}>{lot.id.slice(-2)}</InitialsAvatar>
            <div className="flex min-w-0 flex-col">
              <span className="text-nav font-semibold">{lot.id}</span>
              <span className="truncate text-caption text-muted-foreground">
                {lot.phase} · {formatNumber(lot.headcount)} animaux · {buildingOf(lot)}
              </span>
            </div>
            <div className="flex flex-col gap-1.5">
              <span className="text-caption text-muted-foreground tabular-nums">
                Cible {min}–{max} g/j
              </span>
              <ProgressBar value={(lot.dailyGain / max) * 100} label={`GMQ ${lot.id}`} />
            </div>
            <span className={cn("text-right text-body font-bold whitespace-nowrap tabular-nums", GAIN_TONE[lotHealth(lot)])}>
              {lot.dailyGain ? `${formatNumber(lot.dailyGain)} g/j` : "—"}
            </span>
          </Link>
        )
      })}
    </section>
  )
}
