import { describe, expect, it } from "vitest"
import { resolveAnswers, validateField } from "./validation"
import type { Field } from "./types"

const stepper: Field = {
  key: "count",
  type: "stepper",
  label: "Animaux",
  max: () => 186,
  maxError: (max) => `Le lot ne compte que ${max} animaux`,
}

describe("validateField", () => {
  it("requires a choice", () => {
    expect(validateField({ key: "lot", type: "choice", options: [] }, {})).toBe("Choisissez une option")
    expect(validateField({ key: "lot", type: "choice", options: [] }, { lot: "L-1" })).toBeNull()
  })

  it("names the missing chip group", () => {
    expect(validateField({ key: "cause", type: "chips", label: "Cause probable", options: [] }, {})).toBe("Choisissez « cause probable »")
  })

  it("caps steppers at the lot size with a specific message", () => {
    expect(validateField(stepper, { count: "200" })).toBe("Le lot ne compte que 186 animaux")
    expect(validateField(stepper, { count: "0" })).toBe("Indiquez un nombre supérieur à 0")
    expect(validateField(stepper, { count: "186" })).toBeNull()
  })

  it("allows zero when the stepper minimum is 0", () => {
    expect(validateField({ ...stepper, min: 0 }, { count: "0" })).toBeNull()
  })

  it("rejects non-positive numbers but skips empty optional fields", () => {
    expect(validateField({ key: "kg", type: "number" }, { kg: "abc" })).toBe("Indiquez un nombre supérieur à 0")
    expect(validateField({ key: "kg", type: "number", optional: true }, {})).toBeNull()
  })

  it("runs the field's own rule on text", () => {
    const name: Field = {
      key: "name",
      type: "text",
      label: "Nom du lot",
      validate: (v) => (v === "L-1" ? "Ce nom de lot existe déjà" : null),
    }
    expect(validateField(name, { name: "  " })).toBe("Indiquez nom du lot")
    expect(validateField(name, { name: "L-1" })).toBe("Ce nom de lot existe déjà")
  })

  it("validates against defaults when nothing was typed", () => {
    expect(validateField({ ...stepper, defaultValue: () => "10" }, {})).toBeNull()
  })
})

describe("resolveAnswers", () => {
  it("freezes defaults into the answers", () => {
    const date: Field = { key: "date", type: "date", defaultValue: () => "2026-10-19" }
    expect(resolveAnswers([date, stepper], { count: "5" })).toEqual({ date: "2026-10-19", count: "5" })
  })
})
