import { addDaysTo } from "@/lib/dates"
import { formatDate, formatNumber } from "@/lib/format"
import { GESTATION_DAYS, ULTRASOUND_DAY } from "@/features/reproduction/gestation"
import { chips, dateField, findSow, longDate, num, sowStep } from "./shared"
import type { Flow, FlowContext } from "../types"

const WEANING_DAY = 28
const inDays = (iso: string, days: number) => formatDate(addDaysTo(iso, days), "long")

export function serviceFlow(context: FlowContext): Flow {
  return {
    key: "service",
    title: "Saillie",
    description: "Truie saillie",
    icon: "venus-mars",
    color: "earth",
    saveLabel: "Enregistrer la saillie",
    steps: [
      sowStep(context, "Quelle truie ?", ["empty"]),
      {
        question: "Avec quel verrat, et quand ?",
        fields: [
          { key: "boar", type: "chips", label: "Verrat ou IA", options: chips(["V-03", "V-05", "V-07", "Insémination"]) },
          dateField("Date de la saillie"),
        ],
      },
    ],
    recap: (answers) => {
      const sow = findSow(context, answers)!
      return {
        rows: [
          { label: "Truie", value: `${sow.id} · ${sow.name}` },
          { label: "Verrat", value: answers.boar },
          { label: "Date", value: longDate(answers.date) },
          { label: "Écho à faire", value: inDays(answers.date, ULTRASOUND_DAY) },
          { label: "Mise bas prévue", value: inDays(answers.date, GESTATION_DAYS), emphasis: true },
        ],
        note: "L’écho et la mise bas seront ajoutées automatiquement à vos tâches.",
      }
    },
  }
}

export function ultrasoundFlow(context: FlowContext): Flow {
  return {
    key: "ultrasound",
    title: "Échographie",
    description: "Truie pleine ou vide",
    icon: "stethoscope",
    color: "earth",
    saveLabel: "Enregistrer l’écho",
    steps: [
      sowStep(context, "Quelle truie ?", ["gestating"]),
      {
        question: "Résultat de l’échographie",
        fields: [{ key: "result", type: "chips", label: "Résultat", options: chips(["Pleine", "Vide"]) }, dateField("Date")],
      },
    ],
    recap: (answers) => {
      const sow = findSow(context, answers)!
      return {
        rows: [
          { label: "Truie", value: `${sow.id} · ${sow.name}` },
          { label: "Résultat", value: answers.result, emphasis: true },
          { label: "Date", value: longDate(answers.date) },
        ],
        note:
          answers.result === "Pleine" && sow.servedOn
            ? `Mise bas prévue le ${inDays(sow.servedOn, GESTATION_DAYS)}.`
            : `${sow.id} passera dans « À saillir ».`,
      }
    },
  }
}

export function farrowingFlow(context: FlowContext): Flow {
  return {
    key: "farrowing",
    title: "Mise bas",
    description: "Nouvelle portée",
    icon: "baby",
    color: "earth",
    saveLabel: "Enregistrer la mise bas",
    steps: [
      sowStep(context, "Quelle truie a mis bas ?", ["gestating"]),
      {
        question: "Combien de porcelets ?",
        fields: [
          { key: "bornAlive", type: "stepper", label: "Nés vivants", defaultValue: () => "12", max: () => 30 },
          { key: "stillborn", type: "stepper", label: "Mort-nés", defaultValue: () => "0", min: 0, max: () => 30 },
          dateField("Date"),
        ],
      },
    ],
    recap: (answers) => {
      const sow = findSow(context, answers)!
      return {
        rows: [
          { label: "Truie", value: `${sow.id} · ${sow.name}` },
          { label: "Nés vivants", value: formatNumber(num(answers, "bornAlive")), emphasis: true },
          { label: "Mort-nés", value: formatNumber(num(answers, "stillborn")) },
          { label: "Date", value: longDate(answers.date) },
          { label: "Sevrage prévu", value: inDays(answers.date, WEANING_DAY) },
        ],
        note: `Une portée de ${formatNumber(num(answers, "bornAlive"))} porcelets sera créée en maternité.`,
      }
    },
  }
}
