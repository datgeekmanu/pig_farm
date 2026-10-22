import { addDaysTo, daysSince, type IsoDate } from "@/lib/dates"
import type { Tone } from "@/components/ui/badge"
import type { Sow } from "./types"

// TODO(#7.1): read from farm settings (configurable 100–130 days).
export const GESTATION_DAYS = 114
export const ULTRASOUND_DAY = 28
/** Sows move to the farrowing unit one week before term. */
export const FARROWING_UNIT_DAY = GESTATION_DAYS - 7

export type GestationStage = { label: string; tone: Tone }

export type Gestation = {
  sow: Sow
  day: number
  dueDate: Date
  stage: GestationStage
  /** Next field action: confirm pregnancy, or record the farrowing. */
  nextFlow: "ultrasound" | "farrowing"
  isDueSoon: boolean
}

export function gestationOf(sow: Sow & { servedOn: IsoDate }): Gestation {
  const day = daysSince(sow.servedOn)
  const isDueSoon = day >= FARROWING_UNIT_DAY
  return {
    sow,
    day,
    dueDate: addDaysTo(sow.servedOn, GESTATION_DAYS),
    stage: stageOf(day, Boolean(sow.ultrasoundConfirmed)),
    nextFlow: isDueSoon || sow.ultrasoundConfirmed ? "farrowing" : "ultrasound",
    isDueSoon,
  }
}

function stageOf(day: number, confirmed: boolean): GestationStage {
  if (day >= FARROWING_UNIT_DAY) return { label: "Mise bas proche", tone: "info" }
  if (confirmed) return { label: "Confirmée", tone: "success" }
  if (day > ULTRASOUND_DAY + 4) return { label: "Écho en retard", tone: "danger" }
  if (day >= ULTRASOUND_DAY - 7) return { label: "Écho à faire", tone: "warning" }
  return { label: "Saillie récente", tone: "neutral" }
}

export const isGestating = (sow: Sow): sow is Sow & { servedOn: IsoDate } => sow.status === "gestating" && Boolean(sow.servedOn)
