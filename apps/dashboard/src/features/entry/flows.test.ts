import { describe, expect, it } from "vitest"
import { LOTS, SOWS, STOCK } from "@/mocks/seed"
import { buildFlow, FLOW_ORDER, isFlowKey } from "./flows"
import type { FlowContext } from "./types"

const context: FlowContext = { lots: LOTS.mb, sows: SOWS, stock: STOCK, currency: "XOF" }

describe("flows", () => {
  it.each(FLOW_ORDER)("%s has steps, each with at least one field", (key) => {
    const flow = buildFlow(key, context)
    expect(flow.steps.length).toBeGreaterThan(0)
    for (const step of flow.steps) expect(step.fields.length).toBeGreaterThan(0)
  })

  it("rejects unknown flow keys from the URL", () => {
    expect(isFlowKey("weighing")).toBe(true)
    expect(isFlowKey("constructor")).toBe(false)
  })

  it("offers only empty sows for a service", () => {
    const options = buildFlow("service", context).steps[0].fields[0]
    expect(options.type === "choice" && options.options.map((o) => o.value)).toEqual(["T-112", "T-088"])
  })

  it("offers only free pens for a new lot", () => {
    const pen = buildFlow("new-lot", context).steps[0].fields[1]
    const used = LOTS.mb.map((lot) => lot.pen)
    expect(pen.type === "chips" && pen.options.some((o) => used.includes(o.value))).toBe(false)
  })

  it("summarises a weighing with the new average and a plausibility warning", () => {
    const recap = buildFlow("weighing", context).recap({ lot: "L-24-07", count: "100", totalKg: "11000", date: "2026-10-19" })
    expect(recap.rows.find((row) => row.label === "Poids moyen")?.value).toBe("110,0 kg")
    expect(recap.warning).toMatch(/écart/)
  })

  it("prices a sale in the farm currency", () => {
    const flow = buildFlow("sale", { ...context, currency: "EUR" })
    const recap = flow.recap({
      lot: "L-24-07",
      count: "10",
      totalKg: "1000",
      pricePerKg: "2,5",
      buyer: "B",
      payment: "Espèces",
      status: "Payé",
    })
    expect(recap.rows.find((row) => row.label === "Montant total")?.value.replace(/[  ]/g, " ")).toBe("2 500 €")
  })
})
