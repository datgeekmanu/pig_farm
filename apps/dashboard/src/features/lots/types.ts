import type { IsoDate } from "@/lib/dates"

export type Phase = "Post-sevrage" | "Démarrage" | "Croissance" | "Finition"

export type Lot = {
  id: string
  /** Pen code "<building>-<pen>", e.g. "2-04". */
  pen: string
  phase: Phase
  headcount: number
  initialHeadcount: number
  avgWeightKg: number
  /** Daily weight gain (GMQ), g/day. 0 for a lot not yet weighed. */
  dailyGain: number
  /** Feed conversion ratio (IC). */
  feedConversion: number
  /** Weight uniformity, coefficient of variation in %. */
  uniformityCv: number
  ageDays: number
  startDate: IsoDate
}

export type Building = {
  id: string
  name: string
  description: string
}

export type Weighing = {
  ageWeeks: number
  date: Date
  avgWeightKg: number
  uniformityCv: number
}

export type LotEvent = {
  lotId: string
  icon: string
  text: string
  date: IsoDate
}
