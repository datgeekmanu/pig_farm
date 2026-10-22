import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"
import { db } from "@/mocks/db"
import { entryApi } from "./api"

const scope = { farmId: "mb", currency: "XOF" as const }
const lot = (id: string) => db.read().lots.mb.find((l) => l.id === id)!

describe("entryApi", () => {
  beforeEach(() => vi.useFakeTimers())
  afterEach(() => vi.useRealTimers())

  const submit = async (input: Parameters<typeof entryApi.submit>[0]) => {
    const pending = entryApi.submit(input)
    await vi.runAllTimersAsync()
    return pending
  }

  it("declares deaths, then undo restores the head count", async () => {
    const before = lot("L-24-07").headcount
    const toast = await submit({
      ...scope,
      flow: "mortality",
      answers: { lot: "L-24-07", count: "2", cause: "Digestive", date: "2026-10-19" },
    })

    expect(lot("L-24-07").headcount).toBe(before - 2)
    expect(toast.title).toBe("Mortalité déclarée")

    entryApi.undo()
    await vi.runAllTimersAsync()
    expect(lot("L-24-07").headcount).toBe(before)
  })

  it("records a weighing as the lot's new average and in its history", async () => {
    await submit({ ...scope, flow: "weighing", answers: { lot: "L-24-08", count: "100", totalKg: "5500", date: "2026-10-19" } })
    expect(lot("L-24-08").avgWeightKg).toBe(55)
    expect(db.read().events.at(-1)?.lotId).toBe("L-24-08")
  })

  it("marks the originating task as done", async () => {
    await submit({
      ...scope,
      flow: "stock-in",
      taskId: "low-grower-feed",
      answers: { item: "crois", quantity: "2000", date: "2026-10-19" },
    })
    expect(db.read().doneTasks).toContain("low-grower-feed")
    expect(db.read().stock.find((s) => s.key === "crois")?.quantity).toBe(3240)
  })

  it("stores euro sales in FCFA", async () => {
    await submit({
      farmId: "pe",
      currency: "EUR",
      flow: "sale",
      answers: {
        lot: "P-26-14",
        count: "1",
        totalKg: "100",
        pricePerKg: "1",
        buyer: "B",
        payment: "Espèces",
        status: "Payé",
        date: "2026-10-19",
      },
    })
    expect(db.read().sales[0].amount).toBeCloseTo(65_595.7)
  })
})
