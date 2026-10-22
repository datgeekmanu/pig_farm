import { Input } from "@/components/ui/input"
import { UnitInput } from "@/components/shared/unit-input"
import type { Answers, Field } from "../../types"
import { ChipButton } from "./chip-button"
import { ChoiceField } from "./choice-field"
import { DateField } from "./date-field"
import { StepperField } from "./stepper-field"

type FieldControlProps = {
  field: Field
  value: string
  answers: Answers
  invalid: boolean
  onChange: (value: string) => void
}

/** Renders the input matching a field's type. */
export function FieldControl({ field, value, answers, invalid, onChange }: FieldControlProps) {
  switch (field.type) {
    case "choice":
      return <ChoiceField options={field.options} value={value} onChange={onChange} />
    case "chips":
      return (
        <div className="flex flex-wrap gap-2">
          {field.options.map((option) => (
            <ChipButton key={option.value} selected={value === option.value} onClick={() => onChange(option.value)}>
              {option.label}
            </ChipButton>
          ))}
        </div>
      )
    case "stepper": {
      const max = field.max?.(answers)
      return (
        <StepperField
          label={field.label ?? ""}
          value={value}
          onChange={onChange}
          min={field.min ?? 1}
          max={max}
          fillLabel={max != null ? field.fillLabel?.(max) : undefined}
        />
      )
    }
    case "number":
      return (
        <UnitInput
          size="lg"
          unit={field.unit?.(answers)}
          value={value}
          placeholder={field.placeholder}
          aria-label={field.label}
          aria-invalid={invalid}
          onChange={(event) => onChange(event.target.value)}
        />
      )
    case "text":
      return (
        <Input
          className="h-12"
          value={value}
          placeholder={field.placeholder}
          aria-label={field.label}
          aria-invalid={invalid}
          onChange={(event) => onChange(event.target.value)}
        />
      )
    case "date":
      return <DateField value={value} onChange={onChange} />
  }
}
