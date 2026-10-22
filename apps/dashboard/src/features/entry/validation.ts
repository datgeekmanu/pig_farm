import { parseNumber } from "@/lib/format"
import type { Answers, Field } from "./types"

/** Value shown for a field: the answer if given, else its default. */
export const fieldValue = (field: Field, answers: Answers) => answers[field.key] ?? field.defaultValue?.(answers) ?? ""

type Rule<F extends Field = Field> = (value: string, field: F, answers: Answers) => string | null

const POSITIVE = "Indiquez un nombre supérieur à 0"

function stepperRule(value: string, field: Extract<Field, { type: "stepper" }>, answers: Answers) {
  const count = parseNumber(value)
  if (count < (field.min ?? 1)) return POSITIVE
  const max = field.max?.(answers)
  if (max != null && count > max) return field.maxError?.(max) ?? `Maximum ${max}`
  return null
}

/** One rule per field type; each returns the message to show, or null. */
const RULES: { [T in Field["type"]]: Rule<Field & { type: T }> } = {
  choice: (value) => (value ? null : "Choisissez une option"),
  chips: (value, field) => (value ? null : `Choisissez « ${field.label?.toLowerCase()} »`),
  date: (value) => (value ? null : "Choisissez une date"),
  text: (value, field, answers) => (value.trim() ? (field.validate?.(value, answers) ?? null) : `Indiquez ${field.label?.toLowerCase()}`),
  number: (value) => (parseNumber(value) > 0 ? null : POSITIVE),
  stepper: stepperRule,
}

/** Returns the error message to show under a field, or null when valid. */
export function validateField(field: Field, answers: Answers): string | null {
  const value = fieldValue(field, answers)
  if (field.optional && !value) return null
  const rule = RULES[field.type] as Rule
  return rule(value, field, answers)
}

/** Answers for a step with defaults filled in, ready to store. */
export function resolveAnswers(fields: Field[], answers: Answers): Answers {
  return Object.fromEntries(fields.map((field) => [field.key, fieldValue(field, answers)]))
}
