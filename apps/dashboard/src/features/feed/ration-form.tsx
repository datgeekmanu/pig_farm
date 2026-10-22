"use client"

import { Controller, useForm } from "react-hook-form"
import { zodResolver } from "@hookform/resolvers/zod"
import { z } from "zod"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Card, CardHeader } from "@/components/ui/card"
import { Checkbox } from "@/components/ui/checkbox"
import { Field, FieldError, FieldLabel } from "@/components/ui/field"
import { Input } from "@/components/ui/input"
import { SelectField } from "@/components/shared/select-field"
import { UnitInput } from "@/components/shared/unit-input"
import { useActiveFarm } from "@/features/farms/active-farm"
import { formatMoney, formatNumber, parseNumber } from "@/lib/format"
import { FEED_PHASES, INGREDIENTS } from "./ingredients"
import type { FeedPhase } from "./types"

const rationSchema = z.object({
  name: z.string().trim().min(1, "Indiquez un nom de formule"),
  phase: z.enum(FEED_PHASES as [FeedPhase, ...FeedPhase[]]),
  totalKg: z.string().refine((value) => parseNumber(value) > 0, "Indiquez un poids total supérieur à 0 kg"),
  pool: z.array(z.string()),
})

export type RationRequest = z.infer<typeof rationSchema>

const DEFAULTS: RationRequest = {
  name: "Croissance — octobre",
  phase: "Croissance",
  totalKg: "1000",
  pool: ["mais", "manioc", "son", "soja"],
}

export function RationForm({ onSolve }: { onSolve: (request: RationRequest) => void }) {
  const { farm } = useActiveFarm()
  const { control, register, handleSubmit, formState } = useForm<RationRequest>({
    resolver: zodResolver(rationSchema),
    defaultValues: DEFAULTS,
  })

  return (
    <Card>
      <CardHeader title="Paramètres" icon="sliders" />
      <form onSubmit={handleSubmit(onSolve)} noValidate className="flex flex-col gap-5">
        <Field data-invalid={!!formState.errors.name}>
          <FieldLabel htmlFor="ration-name">
            Nom de la formule
            <Required />
          </FieldLabel>
          <Input id="ration-name" aria-invalid={!!formState.errors.name} {...register("name")} />
          <FieldError errors={[formState.errors.name]} />
        </Field>

        <div className="grid grid-cols-2 gap-3">
          <Controller
            control={control}
            name="phase"
            render={({ field }) => (
              <SelectField
                label="Phase"
                required
                options={FEED_PHASES.map((p) => ({ value: p, label: p }))}
                value={field.value}
                onValueChange={field.onChange}
              />
            )}
          />
          <Field data-invalid={!!formState.errors.totalKg}>
            <FieldLabel htmlFor="ration-total">
              Poids total
              <Required />
            </FieldLabel>
            <UnitInput id="ration-total" unit="kg" aria-invalid={!!formState.errors.totalKg} {...register("totalKg")} />
            <FieldError errors={[formState.errors.totalKg]} />
          </Field>
        </div>

        <fieldset className="flex flex-col gap-1.5">
          <legend className="mb-1.5 text-sm font-semibold">Ingrédients disponibles</legend>
          <Controller
            control={control}
            name="pool"
            render={({ field }) => (
              <>
                {INGREDIENTS.map((ingredient) => {
                  const checked = field.value.includes(ingredient.key)
                  return (
                    <label key={ingredient.key} className="flex cursor-pointer items-center gap-3 rounded-md border bg-card px-3 py-2.5">
                      <Checkbox
                        checked={checked}
                        onCheckedChange={(on) =>
                          field.onChange(on ? [...field.value, ingredient.key] : field.value.filter((key) => key !== ingredient.key))
                        }
                      />
                      <span className="flex flex-1 flex-col">
                        <span className="text-body font-semibold">{ingredient.name}</span>
                        <span className="text-xs text-muted-foreground">
                          PB {formatNumber(ingredient.protein, 1)} % · EM {formatNumber(ingredient.energy)} kcal
                        </span>
                      </span>
                      <span className="text-sm text-muted-foreground tabular-nums">{formatMoney(ingredient.price, farm.currency)}/kg</span>
                    </label>
                  )
                })}
              </>
            )}
          />
          <p className="text-xs text-muted-foreground">Prémix CMV 2,5 % et calcaire 1 % sont incorporés d’office.</p>
        </fieldset>

        <Button type="submit" size="lg">
          <Icon name="calculator" />
          Trouver la ration
        </Button>
      </form>
    </Card>
  )
}

function Required() {
  return (
    <span aria-hidden className="text-destructive">
      *
    </span>
  )
}
