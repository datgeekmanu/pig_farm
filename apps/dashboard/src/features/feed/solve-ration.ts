// Least-cost ration (v1): one energy source + one protein source blended to
// hit the phase's crude protein target, plus fixed premix and limestone.
// TODO(#4.4): replace with the LP solver service; keep this signature.
import { formatNumber } from "@/lib/format"
import { PHASE_REQUIREMENTS } from "./ingredients"
import type { FeedPhase, Ingredient, PhaseRequirement } from "./types"

const PREMIX: Ingredient = { key: "premix", name: "Prémix CMV", price: 1500, energy: 0, protein: 0, lysine: 0 }
const LIMESTONE: Ingredient = { key: "calcaire", name: "Calcaire", price: 60, energy: 0, protein: 0, lysine: 0 }
/** Share of the ration left for energy + protein sources after premix (2,5 %) and limestone (1 %). */
const FREE_SHARE = 0.965
/** Synthetic lysine carried by the premix, %. */
const PREMIX_LYSINE = 0.12
/** Cost of the farm's standard ration (62 % maize, 33,5 % soya, 3,5 % premix), FCFA/kg. */
const STANDARD_COST = 0.62 * 210 + 0.335 * 430 + 0.035 * 1110

export type RationLine = { ingredient: Ingredient; share: number }

export type Ration = {
  ok: true
  lines: RationLine[]
  costPerKg: number
  /** Relative cost vs. the standard ration; negative = cheaper. */
  costVsStandard: number
  nutrients: PhaseRequirement
  requirement: PhaseRequirement
}

export type Infeasible = { ok: false; reason: string }

export function solveRation(phase: FeedPhase, pool: Ingredient[]): Ration | Infeasible {
  const requirement = PHASE_REQUIREMENTS[phase]
  const energySources = pool.filter((i) => i.protein < 20).sort((a, b) => a.price - b.price || b.energy - a.energy)
  const proteinSources = pool.filter((i) => i.protein >= 20).sort((a, b) => a.price / a.protein - b.price / b.protein)

  if (!energySources.length) {
    return { ok: false, reason: "Aucune source d’énergie sélectionnée. Ajoutez le maïs, le manioc ou le son de blé." }
  }
  if (!proteinSources.length) {
    return {
      ok: false,
      reason: `Aucune source de protéines sélectionnée : impossible d’atteindre ${formatNumber(requirement.protein)} % de protéines brutes. Ajoutez le tourteau de soja, la farine de poisson ou les drêches.`,
    }
  }

  const energy = energySources.find((i) => i.energy >= 3000) ?? energySources[0]
  const protein = proteinSources.find((i) => i.protein >= 40) ?? proteinSources[0]

  // Pearson square: share of the energy source in the free part of the ration.
  const target = requirement.protein / FREE_SHARE
  const energyShare = (protein.protein - target) / (protein.protein - energy.protein)
  if (energyShare < 0 || energyShare > 1) {
    return {
      ok: false,
      reason: `${protein.name} ne permet pas d’atteindre ${formatNumber(requirement.protein)} % de protéines brutes. Ajoutez une source plus riche.`,
    }
  }

  const lines: RationLine[] = [
    { ingredient: energy, share: energyShare * FREE_SHARE },
    { ingredient: protein, share: (1 - energyShare) * FREE_SHARE },
    { ingredient: PREMIX, share: 0.025 },
    { ingredient: LIMESTONE, share: 0.01 },
  ]
  const sum = (pick: (i: Ingredient) => number) => lines.reduce((total, line) => total + pick(line.ingredient) * line.share, 0)
  const costPerKg = sum((i) => i.price)

  return {
    ok: true,
    lines,
    costPerKg,
    costVsStandard: costPerKg / STANDARD_COST - 1,
    nutrients: { protein: sum((i) => i.protein), energy: sum((i) => i.energy), lysine: sum((i) => i.lysine) + PREMIX_LYSINE },
    requirement,
  }
}
