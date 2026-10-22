import type { Metadata } from "next"
import { AnimalsScreen } from "@/features/lots/animals-screen"

export const metadata: Metadata = { title: "Animaux" }

export default function Page() {
  return <AnimalsScreen />
}
