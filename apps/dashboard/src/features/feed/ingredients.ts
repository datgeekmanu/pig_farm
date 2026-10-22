import type { FeedPhase, Ingredient, PhaseRequirement } from "./types"

// TODO(#4.1, #4.2): ingredient library and phase requirements from the API.
export const INGREDIENTS: Ingredient[] = [
  { key: "mais", name: "Maïs grain", price: 210, energy: 3300, protein: 8.5, lysine: 0.26 },
  { key: "manioc", name: "Cossettes de manioc", price: 150, energy: 3150, protein: 2.5, lysine: 0.08 },
  { key: "son", name: "Son de blé", price: 120, energy: 1900, protein: 15.5, lysine: 0.6 },
  { key: "dreche", name: "Drêches de brasserie", price: 90, energy: 2100, protein: 24, lysine: 0.9 },
  { key: "soja", name: "Tourteau de soja", price: 430, energy: 2950, protein: 46, lysine: 2.85 },
  { key: "poisson", name: "Farine de poisson", price: 900, energy: 2900, protein: 62, lysine: 4.8 },
]

export const PHASE_REQUIREMENTS: Record<FeedPhase, PhaseRequirement> = {
  Démarrage: { protein: 19, energy: 3150, lysine: 1.15 },
  Croissance: { protein: 17, energy: 3050, lysine: 0.9 },
  Finition: { protein: 15, energy: 3000, lysine: 0.75 },
}

export const FEED_PHASES = Object.keys(PHASE_REQUIREMENTS) as FeedPhase[]
