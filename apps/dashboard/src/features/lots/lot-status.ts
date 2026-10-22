import type { Tone } from "@/components/ui/badge"
import type { LotHealth } from "./growth"

export const LOT_STATUS: Record<LotHealth, { label: string; tone: Tone; curveNote: string }> = {
  new: { label: "Nouveau", tone: "neutral", curveNote: "Pas encore de pesée" },
  good: { label: "Bon", tone: "success", curveNote: "Dans le fuseau IFIP" },
  watch: { label: "À surveiller", tone: "warning", curveNote: "Sous le fuseau IFIP" },
}
