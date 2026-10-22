import { db, respond } from "@/mocks/db"
import type { ExpenseShare, MonthlyFigures } from "./types"

const MONTHS = ["nov.", "déc.", "janv.", "févr.", "mars", "avr.", "mai", "juin", "juil.", "août", "sept.", "oct."]
const REVENUE = [7.4, 9.8, 6.2, 7.1, 8.3, 7.6, 8.2, 6.9, 9.4, 7.8, 10.6, 11.2]
const EXPENSES = [6.0, 6.3, 5.9, 6.1, 6.6, 6.4, 6.1, 6.4, 6.8, 6.2, 7.1, 7.4]

/** Monthly break-even revenue, millions of FCFA. TODO(#6.6): farm setting. */
export const BREAK_EVEN = 7.0

export const PIGS_SOLD: Record<number, number> = { 3: 338, 6: 642, 12: 1284 }

export const EXPENSE_SPLIT: ExpenseShare[] = [
  { name: "Alimentation", share: 0.62, color: "var(--chart-1)" },
  { name: "Main d’œuvre", share: 0.14, color: "var(--chart-4)" },
  { name: "Achat porcelets", share: 0.08, color: "var(--earth-500)" },
  { name: "Vétérinaire", share: 0.07, color: "var(--chart-5)" },
  { name: "Énergie", share: 0.06, color: "var(--chart-2)" },
  { name: "Autre", share: 0.03, color: "var(--neutral-500)" },
]

// TODO(#6.5): GET /finances/overview?months=n and GET /sales.
export const financeApi = {
  monthly: (months: number): MonthlyFigures[] =>
    MONTHS.map((month, i) => ({ month, revenue: REVENUE[i], expenses: EXPENSES[i] })).slice(-months),
  sales: () => respond(db.read().sales),
}
