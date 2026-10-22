import type { Metadata } from "next"
import { FinanceScreen } from "@/features/finance/finance-screen"

export const metadata: Metadata = { title: "Argent" }

export default function Page() {
  return <FinanceScreen />
}
