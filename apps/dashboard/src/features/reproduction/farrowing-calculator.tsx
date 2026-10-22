"use client"

import { useId, useState } from "react"
import { Card, CardHeader } from "@/components/ui/card"
import { Field, FieldLabel } from "@/components/ui/field"
import { DatePicker } from "@/components/shared/date-picker"
import { addDaysTo, daysFromToday, type IsoDate } from "@/lib/dates"
import { formatDate } from "@/lib/format"
import { cn } from "@/lib/utils"
import { FARROWING_UNIT_DAY, GESTATION_DAYS, ULTRASOUND_DAY } from "./gestation"

export function FarrowingCalculator() {
  const id = useId()
  const [servedOn, setServedOn] = useState<IsoDate>(() => daysFromToday(-9))

  const milestones = [
    { label: `Échographie (J${ULTRASOUND_DAY})`, date: formatDate(addDaysTo(servedOn, ULTRASOUND_DAY), "long") },
    { label: `Entrée en maternité (J${FARROWING_UNIT_DAY})`, date: formatDate(addDaysTo(servedOn, FARROWING_UNIT_DAY), "long") },
    { label: `Mise bas prévue (J${GESTATION_DAYS})`, date: formatDate(addDaysTo(servedOn, GESTATION_DAYS), "full"), emphasis: true },
  ]

  return (
    <Card>
      <CardHeader title="Calculateur de mise bas" icon="calendar" />
      <Field>
        <FieldLabel htmlFor={id}>
          Date de saillie
          <span aria-hidden className="text-destructive">
            *
          </span>
        </FieldLabel>
        <DatePicker id={id} value={servedOn} onChange={setServedOn} />
      </Field>
      <dl className="flex flex-col gap-2.5 rounded-lg bg-muted px-4 py-3.5">
        {milestones.map((milestone) => (
          <div key={milestone.label} className="flex justify-between gap-3 text-sm">
            <dt className="text-muted-foreground">{milestone.label}</dt>
            <dd className={cn("text-right first-letter:uppercase", milestone.emphasis ? "font-bold" : "font-medium")}>{milestone.date}</dd>
          </div>
        ))}
      </dl>
      <p className="text-caption text-muted-foreground">
        Durée de gestation réglée à {GESTATION_DAYS} jours dans les paramètres de la ferme.
      </p>
    </Card>
  )
}
