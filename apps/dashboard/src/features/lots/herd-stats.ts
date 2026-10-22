import type { Lot } from "./types"

export type HerdStats = {
  headcount: number
  /** Head-weighted average daily gain, g/day. */
  dailyGain: number
  /** Head-weighted feed conversion ratio. */
  feedConversion: number
  /** Deaths over initial head count. */
  mortalityRate: number
}

export function herdStats(lots: Lot[]): HerdStats {
  const headcount = lots.reduce((total, lot) => total + lot.headcount, 0)
  const weighted = (pick: (lot: Lot) => number) =>
    headcount ? lots.reduce((total, lot) => total + pick(lot) * lot.headcount, 0) / headcount : 0
  const deaths = lots.reduce((total, lot) => total + lot.initialHeadcount - lot.headcount, 0)

  return {
    headcount,
    dailyGain: Math.round(weighted((lot) => lot.dailyGain)),
    feedConversion: weighted((lot) => lot.feedConversion),
    mortalityRate: headcount + deaths ? deaths / (headcount + deaths) : 0,
  }
}

/** Herd targets shown under the overview KPIs. */
export const HERD_TARGETS = { dailyGain: 600, feedConversion: 2.6, mortalityRate: 0.03 }
