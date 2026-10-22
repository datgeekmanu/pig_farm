import { describe, expect, it } from "vitest"
import { formatMoney, formatMoneyCompact, formatNumber, parseNumber, toXof } from "./format"

// fr-FR uses a narrow no-break space (U+202F) as thousands separator.
const nbsp = (s: string) => s.replace(/[  ]/g, " ")

describe("formatNumber", () => {
  it("uses fr-FR separators", () => {
    expect(nbsp(formatNumber(1248.5, 2))).toBe("1 248,50")
  })
})

describe("money", () => {
  it("keeps FCFA amounts as is", () => {
    expect(nbsp(formatMoney(2_450_000, "XOF"))).toBe("2 450 000 FCFA")
  })

  it("converts FCFA to euros at the fixed peg", () => {
    expect(nbsp(formatMoney(655_957, "EUR"))).toBe("1 000 €")
  })

  it("compacts large totals", () => {
    expect(nbsp(formatMoneyCompact(11_200_000, "XOF"))).toBe("11,2 M FCFA")
  })

  it("round-trips euro input to FCFA", () => {
    expect(toXof(10, "EUR")).toBeCloseTo(6559.57)
    expect(toXof(10, "XOF")).toBe(10)
  })
})

describe("parseNumber", () => {
  it.each([
    ["15 960", 15960],
    ["2,5", 2.5],
    ["", 0],
    ["abc", 0],
  ])("parses %j as %d", (input, expected) => {
    expect(parseNumber(input)).toBe(expected)
  })
})
