import Link from "next/link"
import { Badge } from "@/components/ui/badge"
import { InitialsAvatar } from "@/components/shared/initials-avatar"
import { ProgressBar } from "@/components/shared/progress-bar"
import { SectionHeader } from "@/components/shared/section-header"
import { entryHref } from "@/features/entry/links"
import { fromIsoDate } from "@/lib/dates"
import { formatDate } from "@/lib/format"
import { FARROWING_UNIT_DAY, GESTATION_DAYS, ULTRASOUND_DAY, type Gestation } from "./gestation"

const MILESTONES = [(ULTRASOUND_DAY / GESTATION_DAYS) * 100, (FARROWING_UNIT_DAY / GESTATION_DAYS) * 100]

export function GestationList({ gestations }: { gestations: Gestation[] }) {
  return (
    <section className="flex flex-col">
      <SectionHeader
        className="mb-2"
        title="Gestations en cours"
        aside={`Saillie → écho J${ULTRASOUND_DAY} → maternité J${FARROWING_UNIT_DAY} → mise bas J${GESTATION_DAYS}`}
      />
      {gestations.map(({ sow, day, dueDate, stage, nextFlow }) => (
        <Link
          key={sow.id}
          href={entryHref(nextFlow, { sow: sow.id })}
          title={nextFlow === "farrowing" ? "Saisir la mise bas" : "Saisir l’écho"}
          className="grid grid-cols-[48px_minmax(0,140px)_minmax(0,1fr)_120px] items-center gap-4 rounded-md border-t px-2 py-3.5 transition-colors hover:bg-hover-overlay"
        >
          <InitialsAvatar color="earth">{sow.id.slice(2)}</InitialsAvatar>
          <div className="flex min-w-0 flex-col">
            <span className="text-base font-semibold">
              {sow.id} · {sow.name}
            </span>
            <span className="text-caption text-muted-foreground">
              Verrat {sow.boar} · saillie {formatDate(fromIsoDate(sow.servedOn!), "numeric")}
            </span>
          </div>
          <div className="flex flex-col gap-1.5">
            <ProgressBar size="md" value={(day / GESTATION_DAYS) * 100} markers={MILESTONES} label={`Gestation ${sow.id}`} />
            <div className="flex flex-wrap justify-between gap-2">
              <span className="text-caption tabular-nums">
                J{day} / {GESTATION_DAYS}
              </span>
              <Badge tone={stage.tone} size="sm">
                {stage.label}
              </Badge>
            </div>
          </div>
          <div className="flex flex-col text-right">
            <span className="text-xs text-muted-foreground">Mise bas prévue</span>
            <span className="text-body font-bold">{formatDate(dueDate)}</span>
          </div>
        </Link>
      ))}
    </section>
  )
}
