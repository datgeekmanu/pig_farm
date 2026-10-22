import { LOT_COLORS } from "@/components/shared/initials-avatar"
import { buildingOf } from "@/features/lots/growth"
import { daysSince, fromIsoDate, today, toIsoDate } from "@/lib/dates"
import { formatDate, formatNumber, parseNumber } from "@/lib/format"
import type { Answers, ChoiceOption, Field, FlowContext, Step, StepperField } from "../types"
import type { SowStatus } from "@/features/reproduction/types"

export const num = (answers: Answers, key: string) => parseNumber(answers[key])

export const longDate = (iso: string) => formatDate(fromIsoDate(iso), "long")

export const chips = (values: string[]): ChoiceOption[] => values.map((value) => ({ value, label: value }))

export const dateField = (label: string): Field => ({
  key: "date",
  type: "date",
  label,
  defaultValue: () => toIsoDate(today()),
})

export const findLot = (context: FlowContext, answers: Answers) => context.lots.find((lot) => lot.id === answers.lot)

export const findSow = (context: FlowContext, answers: Answers) => context.sows.find((sow) => sow.id === answers.sow)

export function lotStep(context: FlowContext, question: string): Step {
  const options = context.lots.map((lot, i) => ({
    value: lot.id,
    label: `Lot ${lot.id}`,
    sub: `${lot.phase} · ${formatNumber(lot.headcount)} animaux · ${buildingOf(lot)}, loge ${lot.pen}`,
    short: lot.id.slice(-2),
    color: LOT_COLORS[i % LOT_COLORS.length],
  }))
  return { question, fields: [{ key: "lot", type: "choice", options }] }
}

export function sowStep(context: FlowContext, question: string, statuses: SowStatus[]): Step {
  const options = context.sows
    .filter((sow) => statuses.includes(sow.status))
    .map((sow) => ({
      value: sow.id,
      label: `${sow.id} · ${sow.name}`,
      sub: sow.servedOn ? `Saillie le ${formatDate(fromIsoDate(sow.servedOn), "dayMonth")} · J${daysSince(sow.servedOn)}` : sow.note,
      short: sow.id.slice(2),
      color: "earth" as const,
    }))
  return { question, fields: [{ key: "sow", type: "choice", options }] }
}

/** Head-count stepper capped at the lot's current size. */
export function headcountField(context: FlowContext, label: string, defaultValue?: (answers: Answers) => string): StepperField {
  return {
    key: "count",
    type: "stepper",
    label,
    defaultValue,
    max: (answers) => findLot(context, answers)?.headcount,
    maxError: (max) => `Le lot ne compte que ${max} animaux`,
  }
}
