// Growth references used across the herd screens.
import { subDays } from "date-fns"
import { today } from "@/lib/dates"
import type { Lot, Phase, Weighing } from "./types"

/** Daily weight gain target (g/day) per phase: [min, max]. */
export const GAIN_TARGET: Record<Phase, [number, number]> = {
  "Post-sevrage": [380, 450],
  Démarrage: [450, 550],
  Croissance: [700, 800],
  Finition: [780, 880],
}

/** IFIP reference band: [age in weeks, low kg, high kg]. */
const IFIP_BAND: [number, number, number][] = [
  [4, 7, 9],
  [6, 11, 14],
  [8, 16, 20],
  [10, 23, 28],
  [12, 31, 37],
  [14, 40, 47],
  [16, 50, 57],
  [18, 60, 68],
  [20, 70, 79],
  [22, 80, 90],
  [24, 90, 101],
  [26, 100, 112],
]

export const PEN_CAPACITY = 240

export type LotHealth = "new" | "good" | "watch"

/** A lot is "watch" when its daily gain falls under the phase target. */
export function lotHealth(lot: Lot): LotHealth {
  if (!lot.dailyGain) return "new"
  return lot.dailyGain >= GAIN_TARGET[lot.phase][0] ? "good" : "watch"
}

/** IFIP reference range [low, high] kg at a given age, linearly interpolated. */
export function ifipBandAt(ageWeeks: number): [number, number] {
  const last = IFIP_BAND[IFIP_BAND.length - 1]
  if (ageWeeks >= last[0]) return [last[1], last[2]]
  const i = Math.max(
    1,
    IFIP_BAND.findIndex(([week]) => week >= ageWeeks),
  )
  const [weekA, lowA, highA] = IFIP_BAND[i - 1]
  const [weekB, lowB, highB] = IFIP_BAND[i]
  const t = Math.max(0, (ageWeeks - weekA) / (weekB - weekA))
  return [lowA + (lowB - lowA) * t, highA + (highB - highA) * t]
}

const ifipMidpoint = (ageWeeks: number) => {
  const [low, high] = ifipBandAt(ageWeeks)
  return (low + high) / 2
}

/**
 * Fortnightly weighings for a lot, ending with the current state.
 * TODO(#2.3): comes from the weigh-ins API; derived from the IFIP curve for the demo.
 */
export function weighingHistory(lot: Lot): Weighing[] {
  const currentWeek = lot.ageDays / 7
  const scale = lot.avgWeightKg / ifipMidpoint(currentWeek)
  const history: Weighing[] = []

  for (let week = Math.max(4, Math.floor(currentWeek) - 12), i = 0; week < currentWeek - 0.5; week += 2, i++) {
    const jitter = i % 2 ? -0.018 : 0.012
    history.push({
      ageWeeks: week,
      date: subDays(today(), Math.round((currentWeek - week) * 7)),
      avgWeightKg: +(ifipMidpoint(week) * scale * (1 + jitter)).toFixed(1),
      uniformityCv: +(lot.uniformityCv + (currentWeek - week) * 0.35).toFixed(1),
    })
  }
  history.push({ ageWeeks: +currentWeek.toFixed(1), date: today(), avgWeightKg: lot.avgWeightKg, uniformityCv: lot.uniformityCv })
  return history
}

/** Daily gain (g/day) between two weighings. */
export const gainBetween = (from: Weighing, to: Weighing) =>
  Math.round(((to.avgWeightKg - from.avgWeightKg) * 1000) / ((to.ageWeeks - from.ageWeeks) * 7))

export const buildingOf = (lot: Pick<Lot, "pen">) => `Bâtiment ${lot.pen[0]}`

/** Phase a newly created lot starts in, from its average entry weight. */
export function phaseForWeight(kg: number): Phase {
  if (kg < 15) return "Post-sevrage"
  if (kg < 30) return "Démarrage"
  if (kg < 60) return "Croissance"
  return "Finition"
}
