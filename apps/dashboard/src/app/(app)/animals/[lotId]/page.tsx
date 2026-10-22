import type { Metadata } from "next"
import { LotDetailScreen } from "@/features/lots/lot-detail/lot-detail-screen"

export const metadata: Metadata = { title: "Fiche lot" }

export default function Page() {
  return <LotDetailScreen />
}
