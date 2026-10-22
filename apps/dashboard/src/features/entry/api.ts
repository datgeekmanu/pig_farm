// Writes for each guided entry. TODO(#2–#6): one POST per flow on the Laravel API.
import { db, respond } from "@/mocks/db"
import { phaseForWeight } from "@/features/lots/growth"
import { formatDate, formatNumber, parseNumber, toXof } from "@/lib/format"
import { addDaysTo, fromIsoDate } from "@/lib/dates"
import { GESTATION_DAYS } from "@/features/reproduction/gestation"
import type { Currency } from "@/features/farms/types"
import type { Answers, FlowKey } from "./types"

type Draft = ReturnType<typeof db.read>
type Toast = { title: string; description: string }
type Write = (draft: Draft, answers: Answers, scope: { farmId: string; currency: Currency }) => Toast

const n = (answers: Answers, key: string) => parseNumber(answers[key])
const shortDate = (iso: string) => formatDate(fromIsoDate(iso), "numeric")

function updateLot(draft: Draft, farmId: string, lotId: string, change: (lot: Draft["lots"][string][number]) => void) {
  const lot = draft.lots[farmId].find((l) => l.id === lotId)!
  change(lot)
  return lot
}

const WRITES: Record<FlowKey, Write> = {
  weighing: (draft, answers, { farmId }) => {
    const average = +(n(answers, "totalKg") / n(answers, "count")).toFixed(1)
    const lot = updateLot(draft, farmId, answers.lot, (l) => (l.avgWeightKg = average))
    draft.events.push({
      lotId: lot.id,
      icon: "weight-scale",
      text: `Pesée : ${formatNumber(average, 1)} kg de moyenne (${formatNumber(n(answers, "count"))} animaux)`,
      date: answers.date,
    })
    return { title: "Pesée enregistrée", description: `Lot ${lot.id} · ${formatNumber(average, 1)} kg de moyenne` }
  },

  mortality: (draft, answers, { farmId }) => {
    const deaths = n(answers, "count")
    const lot = updateLot(draft, farmId, answers.lot, (l) => (l.headcount -= deaths))
    draft.events.push({
      lotId: lot.id,
      icon: "skull-crossbones",
      text: `${deaths} mort${deaths > 1 ? "s" : ""} · cause ${answers.cause.toLowerCase()}`,
      date: answers.date,
    })
    return { title: "Mortalité déclarée", description: `Lot ${lot.id} · ${formatNumber(lot.headcount)} animaux présents` }
  },

  treatment: (draft, answers) => {
    draft.events.push({
      lotId: answers.lot,
      icon: "syringe",
      text: `${answers.product} · ${formatNumber(n(answers, "dose"), 1)} ml/animal · ${answers.caregiver}`,
      date: answers.date,
    })
    return { title: "Traitement enregistré", description: `${answers.product} · lot ${answers.lot}` }
  },

  "new-lot": (draft, answers, { farmId }) => {
    const avgWeightKg = n(answers, "avgKg") || 8
    draft.lots[farmId].push({
      id: answers.name.trim(),
      pen: answers.pen,
      phase: phaseForWeight(avgWeightKg),
      headcount: n(answers, "count"),
      initialHeadcount: n(answers, "count"),
      avgWeightKg,
      dailyGain: 0,
      feedConversion: 0,
      uniformityCv: 15,
      ageDays: Math.max(28, Math.round(avgWeightKg * 3.2)),
      startDate: answers.date,
    })
    return { title: "Lot créé", description: `${answers.name} · ${formatNumber(n(answers, "count"))} animaux en loge ${answers.pen}` }
  },

  service: (draft, answers) => {
    const sow = draft.sows.find((s) => s.id === answers.sow)!
    Object.assign(sow, { status: "gestating", servedOn: answers.date, boar: answers.boar, ultrasoundConfirmed: false, note: undefined })
    return {
      title: "Saillie enregistrée",
      description: `${sow.id} · mise bas prévue le ${formatDate(addDaysTo(answers.date, GESTATION_DAYS))}`,
    }
  },

  ultrasound: (draft, answers) => {
    const sow = draft.sows.find((s) => s.id === answers.sow)!
    if (answers.result === "Pleine") sow.ultrasoundConfirmed = true
    else Object.assign(sow, { status: "empty", servedOn: undefined, note: `Écho vide le ${shortDate(answers.date)}` })
    return { title: "Échographie enregistrée", description: `${sow.id} · ${answers.result.toLowerCase()}` }
  },

  farrowing: (draft, answers) => {
    const sow = draft.sows.find((s) => s.id === answers.sow)!
    Object.assign(sow, {
      status: "lactating",
      servedOn: undefined,
      note: `Mise bas le ${shortDate(answers.date)} · ${n(answers, "bornAlive")} porcelets`,
    })
    return { title: "Mise bas enregistrée", description: `${sow.id} · ${n(answers, "bornAlive")} porcelets nés vivants` }
  },

  sale: (draft, answers, { farmId, currency }) => {
    const count = n(answers, "count")
    updateLot(draft, farmId, answers.lot, (l) => (l.headcount -= count))
    draft.sales.unshift({
      id: crypto.randomUUID(),
      date: formatDate(fromIsoDate(answers.date), "dayMonth"),
      buyer: answers.buyer,
      items: `${count} porcs`,
      weightKg: n(answers, "totalKg"),
      amount: toXof(n(answers, "totalKg") * n(answers, "pricePerKg"), currency),
      status: answers.status,
      tone: answers.status === "Payé" ? "success" : answers.status === "Partiel" ? "warning" : "neutral",
    })
    draft.events.push({ lotId: answers.lot, icon: "sack-dollar", text: `${count} animaux vendus à ${answers.buyer}`, date: answers.date })
    return { title: "Vente enregistrée", description: `${count} animaux · ${answers.buyer}` }
  },

  // TODO(#6.2): expenses have no list screen yet; the finance figures are static.
  expense: (_draft, answers) => ({ title: "Dépense enregistrée", description: answers.category }),

  "stock-in": (draft, answers) => {
    const item = draft.stock.find((s) => s.key === answers.item)!
    item.quantity += n(answers, "quantity")
    return { title: "Stock mis à jour", description: `${item.name} · ${formatNumber(item.quantity)} ${item.unit}` }
  },
}

export type EntryInput = {
  flow: FlowKey
  answers: Answers
  farmId: string
  currency: Currency
  /** Task from "À faire aujourd'hui" that this entry completes. */
  taskId?: string
}

export const entryApi = {
  submit: ({ flow, answers, farmId, currency, taskId }: EntryInput) =>
    respond(
      db.write((draft) => {
        if (taskId) draft.doneTasks.push(taskId)
        return WRITES[flow](draft, answers, { farmId, currency })
      }),
    ),
  undo: () => respond(db.undo()),
}
