import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"
import { LOTS } from "@/mocks/seed"
import { gainBetween, ifipBandAt, lotHealth, phaseForWeight, weighingHistory } from "./growth"
import { herdStats } from "./herd-stats"
import type { Lot } from "./types"

const lot = (overrides: Partial<Lot>): Lot => ({ ...LOTS.mb[0], ...overrides })

describe("lotHealth", () => {
  it("flags a lot growing under its phase target", () => {
    expect(lotHealth(lot({ phase: "Croissance", dailyGain: 650 }))).toBe("watch")
    expect(lotHealth(lot({ phase: "Croissance", dailyGain: 731 }))).toBe("good")
    expect(lotHealth(lot({ dailyGain: 0 }))).toBe("new")
  })
})

describe("ifipBandAt", () => {
  it("returns the table values on known weeks", () => {
    expect(ifipBandAt(10)).toEqual([23, 28])
  })

  it("interpolates between weeks and clamps past the end", () => {
    expect(ifipBandAt(11)).toEqual([27, 32.5])
    expect(ifipBandAt(40)).toEqual([100, 112])
  })
})

describe("weighingHistory", () => {
  beforeEach(() => vi.useFakeTimers({ now: new Date(2026, 9, 19) }))
  afterEach(() => vi.useRealTimers())

  it("ends with the lot's current weight and is chronological", () => {
    const history = weighingHistory(LOTS.mb[0])
    expect(history.at(-1)?.avgWeightKg).toBe(LOTS.mb[0].avgWeightKg)
    const weeks = history.map((w) => w.ageWeeks)
    expect(weeks).toEqual([...weeks].sort((a, b) => a - b))
  })

  it("computes daily gain between weighings", () => {
    const from = { ageWeeks: 10, avgWeightKg: 25, uniformityCv: 10, date: new Date() }
    const to = { ...from, ageWeeks: 12, avgWeightKg: 35 }
    expect(gainBetween(from, to)).toBe(714)
  })
})

describe("phaseForWeight", () => {
  it.each([
    [8, "Post-sevrage"],
    [20, "Démarrage"],
    [45, "Croissance"],
    [80, "Finition"],
  ] as const)("%i kg → %s", (kg, phase) => {
    expect(phaseForWeight(kg)).toBe(phase)
  })
})

describe("herdStats", () => {
  it("weights averages by head count", () => {
    const stats = herdStats([
      lot({ headcount: 100, initialHeadcount: 100, dailyGain: 800 }),
      lot({ headcount: 300, initialHeadcount: 300, dailyGain: 400 }),
    ])
    expect(stats.headcount).toBe(400)
    expect(stats.dailyGain).toBe(500)
  })

  it("computes mortality over the initial head count", () => {
    expect(herdStats([lot({ headcount: 97, initialHeadcount: 100 })]).mortalityRate).toBeCloseTo(0.03)
  })

  it("handles an empty farm", () => {
    expect(herdStats([])).toEqual({ headcount: 0, dailyGain: 0, feedConversion: 0, mortalityRate: 0 })
  })
})
