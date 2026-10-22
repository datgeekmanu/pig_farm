import { addDays } from "date-fns"
import { Badge } from "@/components/ui/badge"
import { SectionHeader } from "@/components/shared/section-header"
import { today } from "@/lib/dates"
import { formatDate } from "@/lib/format"
import type { Treatment } from "./types"

export function TreatmentList({ treatments }: { treatments: Treatment[] }) {
  return (
    <section className="flex flex-col gap-3">
      {/* TODO(#5.4): prophylaxis calendar screen */}
      <SectionHeader title="Traitements à venir" aside={<span className="text-body text-foreground">Calendrier prophylaxie</span>} />
      {treatments.map((treatment) => {
        const date = addDays(today(), treatment.inDays)
        return (
          <div
            key={`${treatment.product}-${treatment.target}`}
            className="grid grid-cols-[64px_minmax(0,1fr)_auto] items-center gap-3.5 border-t py-3"
          >
            <div className="flex h-14 w-16 flex-col items-center justify-center rounded-lg bg-muted">
              <span className="text-xs text-muted-foreground">{formatDate(date, "weekday")}</span>
              <span className="font-heading text-lg font-bold">{date.getDate()}</span>
            </div>
            <div className="flex min-w-0 flex-col">
              <span className="text-body font-semibold">
                {treatment.product} · {treatment.target}
              </span>
              <span className="text-caption text-muted-foreground">
                {treatment.type} · {treatment.dose} · {treatment.caregiver}
              </span>
            </div>
            <Badge tone={treatment.urgency} size="sm">
              {treatment.tag}
            </Badge>
          </div>
        )
      })}
    </section>
  )
}
