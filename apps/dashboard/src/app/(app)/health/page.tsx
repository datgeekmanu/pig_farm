import type { Metadata } from "next"
import { HealthScreen } from "@/features/health/health-screen"

export const metadata: Metadata = { title: "Santé et stocks" }

export default function Page() {
  return <HealthScreen />
}
