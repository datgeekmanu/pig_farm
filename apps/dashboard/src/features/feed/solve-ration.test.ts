import { describe, expect, it } from "vitest"
import { INGREDIENTS } from "./ingredients"
import { solveRation } from "./solve-ration"

const pick = (...keys: string[]) => INGREDIENTS.filter((i) => keys.includes(i.key))

describe("solveRation", () => {
  it("finds a ration that sums to 100 % and meets the protein target", () => {
    const result = solveRation("Croissance", pick("mais", "manioc", "son", "soja"))
    if (!result.ok) throw new Error(result.reason)

    const total = result.lines.reduce((sum, line) => sum + line.share, 0)
    expect(total).toBeCloseTo(1)
    expect(result.nutrients.protein).toBeCloseTo(result.requirement.protein, 5)
  })

  it("picks the cheapest energy source above 3000 kcal and the cheapest protein per point", () => {
    const result = solveRation("Croissance", pick("mais", "manioc", "soja", "poisson"))
    if (!result.ok) throw new Error(result.reason)
    expect(result.lines.map((line) => line.ingredient.key).slice(0, 2)).toEqual(["manioc", "soja"])
  })

  it("is cheaper than the standard ration with local by-products", () => {
    const result = solveRation("Croissance", pick("manioc", "soja"))
    if (!result.ok) throw new Error(result.reason)
    expect(result.costVsStandard).toBeLessThan(0)
  })

  it("refuses a pool without an energy source", () => {
    const result = solveRation("Finition", pick("soja"))
    expect(result.ok).toBe(false)
    if (!result.ok) expect(result.reason).toMatch(/énergie/)
  })

  it("refuses a pool without a protein source instead of crashing", () => {
    const result = solveRation("Démarrage", pick("mais", "manioc"))
    expect(result.ok).toBe(false)
    if (!result.ok) expect(result.reason).toMatch(/19 % de protéines/)
  })

  it("refuses a blend whose energy source alone overshoots the protein target", () => {
    const richBran = { key: "bran", name: "Son enrichi", price: 100, energy: 3100, protein: 19.5, lysine: 0.5 }
    const result = solveRation("Finition", [richBran, ...pick("dreche")])
    expect(result.ok).toBe(false)
    if (!result.ok) expect(result.reason).toMatch(/ne permet pas/)
  })
})
