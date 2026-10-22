import type { Tone } from "@/components/ui/badge"
import type { StockItem } from "./types"

export type StockLevel = { tone: Tone; label: string; isLow: boolean }

/** "Bientôt bas" when within 25 % above the alert threshold. */
export function stockLevel(item: StockItem): StockLevel {
  if (item.quantity < item.alertThreshold) return { tone: "danger", label: "Stock bas", isLow: true }
  if (item.quantity < item.alertThreshold * 1.25) return { tone: "warning", label: "Bientôt bas", isLow: false }
  return { tone: "success", label: "Suffisant", isLow: false }
}
