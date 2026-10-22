import type { Metadata } from "next"
import { EntryScreen } from "@/features/entry/components/entry-screen"

export const metadata: Metadata = { title: "Saisir" }

export default function Page() {
  return <EntryScreen />
}
