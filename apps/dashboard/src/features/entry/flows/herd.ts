import { BUILDINGS } from "@/mocks/seed"
import { PEN_CAPACITY } from "@/features/lots/growth"
import { formatNumber } from "@/lib/format"
import { chips, dateField, findLot, headcountField, longDate, lotStep, num } from "./shared"
import type { Flow, FlowContext } from "../types"

export function weighingFlow(context: FlowContext): Flow {
  return {
    key: "weighing",
    title: "Pesée",
    description: "Poids d’un lot",
    icon: "weight-scale",
    color: "petrol",
    saveLabel: "Enregistrer la pesée",
    steps: [
      lotStep(context, "Quel lot avez-vous pesé ?"),
      {
        question: "Combien d’animaux et quel poids total ?",
        fields: [
          {
            ...headcountField(context, "Animaux pesés", (answers) => String(findLot(context, answers)?.headcount ?? "")),
            fillLabel: (max) => `Tout le lot (${max})`,
          },
          { key: "totalKg", type: "number", label: "Poids total de la pesée", unit: () => "kg", placeholder: "ex. 15 960" },
          dateField("Date de la pesée"),
        ],
      },
    ],
    recap: (answers) => {
      const lot = findLot(context, answers)!
      const average = num(answers, "totalKg") / num(answers, "count")
      const change = average - lot.avgWeightKg
      return {
        rows: [
          { label: "Lot", value: lot.id },
          { label: "Animaux pesés", value: formatNumber(num(answers, "count")) },
          { label: "Poids total", value: `${formatNumber(num(answers, "totalKg"))} kg` },
          { label: "Poids moyen", value: `${formatNumber(average, 1)} kg`, emphasis: true },
          { label: "Depuis la dernière pesée", value: `${change >= 0 ? "+" : "−"}${formatNumber(Math.abs(change), 1)} kg` },
          { label: "Date", value: longDate(answers.date) },
        ],
        note: `Le poids moyen du lot passera de ${formatNumber(lot.avgWeightKg, 1)} kg à ${formatNumber(average, 1)} kg.`,
        warning: Math.abs(change) > 20 ? "L’écart avec la dernière pesée est important. Vérifiez le poids total." : undefined,
      }
    },
  }
}

export function mortalityFlow(context: FlowContext): Flow {
  return {
    key: "mortality",
    title: "Mortalité",
    description: "Animal mort",
    icon: "skull-crossbones",
    color: "neutral",
    saveLabel: "Déclarer la mortalité",
    steps: [
      lotStep(context, "Dans quel lot ?"),
      {
        question: "Combien d’animaux et pourquoi ?",
        fields: [
          headcountField(context, "Nombre de morts", () => "1"),
          {
            key: "cause",
            type: "chips",
            label: "Cause probable",
            options: chips(["Respiratoire", "Digestive", "Écrasement", "Accident", "Inconnue"]),
          },
          dateField("Date"),
        ],
      },
    ],
    recap: (answers) => {
      const lot = findLot(context, answers)!
      const deaths = num(answers, "count")
      return {
        rows: [
          { label: "Lot", value: lot.id },
          { label: "Nombre", value: formatNumber(deaths), emphasis: true },
          { label: "Cause", value: answers.cause },
          { label: "Date", value: longDate(answers.date) },
        ],
        note: `L’effectif du lot passera de ${formatNumber(lot.headcount)} à ${formatNumber(lot.headcount - deaths)} animaux.`,
      }
    },
  }
}

const RECALL_DAYS: Record<string, number> = { "Dans 7 jours": 7, "Dans 14 jours": 14, "Dans 21 jours": 21 }

export function treatmentFlow(context: FlowContext): Flow {
  return {
    key: "treatment",
    title: "Traitement",
    description: "Vaccin, soin",
    icon: "syringe",
    color: "teal",
    saveLabel: "Enregistrer le traitement",
    steps: [
      lotStep(context, "Quel lot avez-vous traité ?"),
      {
        question: "Quel produit, quelle dose ?",
        fields: [
          {
            key: "product",
            type: "chips",
            label: "Produit",
            options: chips(["Vaccin PPC", "Ivermectine 1 %", "Fer dextran", "Amoxicilline", "Vermifuge"]),
          },
          { key: "dose", type: "number", label: "Dose par animal", unit: () => "ml", placeholder: "ex. 2" },
          { key: "caregiver", type: "chips", label: "Soigné par", options: chips(["Dr Ateba", "Jean Mvogo", "Paul Essomba"]) },
        ],
      },
      {
        question: "Quand, et faut-il un rappel ?",
        fields: [
          dateField("Date du traitement"),
          { key: "recall", type: "chips", label: "Rappel", options: chips(["Aucun", ...Object.keys(RECALL_DAYS)]) },
        ],
      },
    ],
    recap: (answers) => {
      const lot = findLot(context, answers)!
      const recallDays = RECALL_DAYS[answers.recall]
      return {
        rows: [
          { label: "Lot", value: `${lot.id} · ${formatNumber(lot.headcount)} animaux` },
          { label: "Produit", value: answers.product, emphasis: true },
          { label: "Dose", value: `${formatNumber(num(answers, "dose"), 1)} ml par animal` },
          { label: "Soigné par", value: answers.caregiver },
          { label: "Date", value: longDate(answers.date) },
          { label: "Rappel", value: recallDays ? `Dans ${recallDays} jours` : "Aucun" },
        ],
        note: recallDays ? "Le rappel apparaîtra dans « Aujourd’hui » le jour prévu." : undefined,
      }
    },
  }
}

export function newLotFlow(context: FlowContext): Flow {
  const usedPens = new Set(context.lots.map((lot) => lot.pen))
  const freePens = BUILDINGS.flatMap((building) => Array.from({ length: 8 }, (_, i) => `${building.id}-0${i + 1}`))
    .filter((pen) => !usedPens.has(pen))
    .slice(0, 8)

  return {
    key: "new-lot",
    title: "Nouveau lot",
    description: "Animaux entrés",
    icon: "layer-group",
    color: "petrol",
    saveLabel: "Créer le lot",
    steps: [
      {
        question: "Comment s’appelle le lot, et où est-il ?",
        fields: [
          {
            key: "name",
            type: "text",
            label: "Nom du lot",
            placeholder: "ex. L-24-12",
            validate: (value) => (context.lots.some((lot) => lot.id === value.trim()) ? "Ce nom de lot existe déjà" : null),
          },
          {
            key: "pen",
            type: "chips",
            label: "Loge libre",
            options: freePens.map((pen) => ({ value: pen, label: `Bât. ${pen[0]} · ${pen}` })),
          },
        ],
      },
      {
        question: "Combien d’animaux sont entrés ?",
        fields: [
          {
            key: "count",
            type: "stepper",
            label: "Effectif initial",
            defaultValue: () => "200",
            max: () => PEN_CAPACITY,
            maxError: () => `Une loge accueille au plus ${PEN_CAPACITY} animaux`,
          },
          { key: "avgKg", type: "number", label: "Poids moyen à l’entrée", unit: () => "kg", placeholder: "ex. 8", optional: true },
          dateField("Date de mise en lot"),
        ],
      },
    ],
    recap: (answers) => ({
      rows: [
        { label: "Nom", value: answers.name, emphasis: true },
        { label: "Loge", value: answers.pen },
        { label: "Effectif", value: `${formatNumber(num(answers, "count"))} animaux` },
        { label: "Poids moyen", value: answers.avgKg ? `${formatNumber(num(answers, "avgKg"), 1)} kg` : "—" },
        { label: "Date", value: longDate(answers.date) },
      ],
    }),
  }
}
