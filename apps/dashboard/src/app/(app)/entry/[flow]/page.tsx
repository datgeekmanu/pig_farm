import type { Metadata } from "next"
import { EntryWizard } from "@/features/entry/components/entry-wizard"

export const metadata: Metadata = { title: "Saisie guidée" }

export default function Page() {
  return <EntryWizard />
}
