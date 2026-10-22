import type { AvatarColor } from "@/components/shared/initials-avatar"
import type { Lot } from "@/features/lots/types"
import type { Sow } from "@/features/reproduction/types"
import type { StockItem } from "@/features/health/types"
import type { Currency } from "@/features/farms/types"

/** Raw answers keyed by field, always strings (as typed or picked). */
export type Answers = Record<string, string>

/** Live farm data the questions are built from. */
export type FlowContext = {
  lots: Lot[]
  sows: Sow[]
  stock: StockItem[]
  currency: Currency
}

export type ChoiceOption = {
  value: string
  label: string
  sub?: string
  /** Short code shown in the avatar circle. */
  short?: string
  color?: AvatarColor
}

type FieldBase = {
  key: string
  label?: string
  optional?: boolean
  placeholder?: string
  defaultValue?: (answers: Answers) => string
  /** Extra rule on top of the built-in "required / positive / max" checks. */
  validate?: (value: string, answers: Answers) => string | null
}

export type Field =
  | (FieldBase & { type: "choice" | "chips"; options: ChoiceOption[] })
  | (FieldBase & { type: "text" | "date" })
  | (FieldBase & { type: "number"; unit?: (answers: Answers) => string | undefined })
  | (FieldBase & {
      type: "stepper"
      min?: number
      max?: (answers: Answers) => number | undefined
      maxError?: (max: number) => string
      /** Label for the "take everything" shortcut, e.g. "Tout le lot (186)". */
      fillLabel?: (max: number) => string
    })

export type StepperField = Extract<Field, { type: "stepper" }>

export type Step = { question: string; fields: Field[] }

export type RecapRow = { label: string; value: string; emphasis?: boolean }

export type Recap = {
  rows: RecapRow[]
  /** What will change once saved. */
  note?: string
  /** Plausibility warning; does not block saving. */
  warning?: string
}

export type FlowKey =
  "weighing" | "mortality" | "treatment" | "new-lot" | "service" | "ultrasound" | "farrowing" | "sale" | "expense" | "stock-in"

export type Flow = {
  key: FlowKey
  title: string
  description: string
  icon: string
  color: AvatarColor
  saveLabel: string
  ownerOnly?: boolean
  steps: Step[]
  recap: (answers: Answers) => Recap
}
