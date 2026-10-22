import type { Tone } from "@/components/ui/badge"

export type MonthlyFigures = {
  month: string
  /** Turnover and expenses in millions of FCFA. */
  revenue: number
  expenses: number
}

export type ExpenseShare = {
  name: string
  share: number
  color: string
}

export type Sale = {
  id: string
  date: string
  buyer: string
  items: string
  weightKg: number
  /** Amount in FCFA. */
  amount: number
  status: string
  tone: Tone
}
