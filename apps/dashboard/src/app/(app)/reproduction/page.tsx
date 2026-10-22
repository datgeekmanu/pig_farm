import type { Metadata } from "next"
import { ReproductionScreen } from "@/features/reproduction/reproduction-screen"

export const metadata: Metadata = { title: "Reproduction" }

export default function Page() {
  return <ReproductionScreen />
}
