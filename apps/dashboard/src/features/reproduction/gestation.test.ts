import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"
import { daysFromToday } from "@/lib/dates"
import { gestationOf, GESTATION_DAYS, isGestating } from "./gestation"
import type { Sow } from "./types"

const sow = (daysAgo: number, ultrasoundConfirmed = false) =>
  ({ id: "T-1", name: "Test", status: "gestating", servedOn: daysFromToday(-daysAgo), boar: "V-1", ultrasoundConfirmed }) as Sow & {
    servedOn: string
  }

describe("gestationOf", () => {
  beforeEach(() => vi.useFakeTimers({ now: new Date(2026, 9, 19) }))
  afterEach(() => vi.useRealTimers())

  it("predicts farrowing 114 days after service", () => {
    const g = gestationOf(sow(10))
    expect(g.day).toBe(10)
    expect(g.dueDate).toEqual(new Date(2026, 9, 19 + GESTATION_DAYS - 10))
  })

  it.each([
    [5, false, "Saillie récente", "ultrasound"],
    [25, false, "Écho à faire", "ultrasound"],
    [44, false, "Écho en retard", "ultrasound"],
    [60, true, "Confirmée", "farrowing"],
    [110, true, "Mise bas proche", "farrowing"],
  ])("day %i (confirmed: %s) → %s, next %s", (days, confirmed, label, next) => {
    const g = gestationOf(sow(days, confirmed))
    expect(g.stage.label).toBe(label)
    expect(g.nextFlow).toBe(next)
  })

  it("only treats served, gestating sows as gestations", () => {
    expect(isGestating({ id: "T-2", name: "Vide", status: "empty" })).toBe(false)
    expect(isGestating(sow(1))).toBe(true)
  })
})
