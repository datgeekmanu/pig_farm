export type StockCategory = "feed" | "vet"

export type StockItem = {
  key: string
  name: string
  location: string
  quantity: number
  alertThreshold: number
  unit: string
  /** Unit price in FCFA. */
  unitPrice: number
  /** Average daily usage, used to estimate days left. */
  dailyUsage: number
  icon: string
  category: StockCategory
}

export type Treatment = {
  inDays: number
  product: string
  target: string
  type: string
  dose: string
  caregiver: string
  urgency: "danger" | "warning" | "neutral"
  tag: string
}
