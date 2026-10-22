export type FeedPhase = "Démarrage" | "Croissance" | "Finition"

export type Ingredient = {
  key: string
  name: string
  /** Price in FCFA per kg. */
  price: number
  /** Metabolisable energy, kcal/kg. */
  energy: number
  /** Crude protein, %. */
  protein: number
  /** Lysine, %. */
  lysine: number
}

export type PhaseRequirement = Pick<Ingredient, "energy" | "protein" | "lysine">
