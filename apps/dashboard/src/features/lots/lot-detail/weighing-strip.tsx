import { SectionHeader } from "@/components/shared/section-header"
import { formatDate, formatNumber } from "@/lib/format"
import { cn } from "@/lib/utils"
import type { Weighing } from "../types"

type WeighingStripProps = {
  lotId: string
  weighings: Weighing[]
  selectedIndex: number
  onSelect: (index: number) => void
}

export function WeighingStrip({ lotId, weighings, selectedIndex, onSelect }: WeighingStripProps) {
  return (
    <section className="flex flex-col gap-2.5">
      <SectionHeader size="md" title={`Pesées · ${lotId}`} aside={`${weighings.length} pesées`} />
      <div className="flex gap-2 overflow-x-auto pb-1">
        {weighings.map((weighing, index) => (
          <button
            key={weighing.ageWeeks}
            type="button"
            aria-pressed={index === selectedIndex}
            onClick={() => onSelect(index)}
            className={cn(
              "flex min-w-28 flex-none cursor-pointer flex-col items-start gap-0.5 rounded-lg border border-l-4 bg-card px-3.5 py-2.5 transition-colors hover:border-ring",
              index === selectedIndex ? "border-ring border-l-live" : "border-l-border",
            )}
          >
            <span className="text-xs text-muted-foreground">S{Math.round(weighing.ageWeeks)} d’âge</span>
            <span className="text-body font-bold">{formatDate(weighing.date, "dayMonth")}</span>
            <span className="text-caption text-muted-foreground tabular-nums">{formatNumber(weighing.avgWeightKg, 1)} kg</span>
          </button>
        ))}
      </div>
    </section>
  )
}
