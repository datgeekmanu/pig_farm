import { afterEach, beforeEach, describe, expect, it, vi } from "vitest"
import { LOTS, SOWS, STOCK } from "@/mocks/seed"
import { careTasks, reproductionTasks, stockTasks } from "./today-tasks"

describe("today's tasks", () => {
  beforeEach(() => vi.useFakeTimers({ now: new Date() }))
  afterEach(() => vi.useRealTimers())

  it("raises a low-stock task that pre-fills the stock entry", () => {
    const [task] = stockTasks(STOCK)
    expect(task.title).toBe("Aliment croissance bas")
    expect(task.href).toBe("/entry/stock-in?item=crois&task=low-grower-feed")
  })

  it("stays quiet when the stock is above its threshold", () => {
    expect(stockTasks(STOCK.map((item) => ({ ...item, quantity: item.alertThreshold })))).toEqual([])
  })

  it("schedules the PPC recall on a lot", () => {
    expect(careTasks(LOTS.mb)[0].href).toContain("product=Vaccin+PPC")
    expect(careTasks([])).toEqual([])
  })

  it("lists due farrowings, late ultrasounds and sows to serve", () => {
    const ids = reproductionTasks(SOWS).map((task) => task.id)
    expect(ids).toEqual(["farrowing-T-118", "ultrasound-T-133", "service-T-112", "service-T-088"])
  })
})
