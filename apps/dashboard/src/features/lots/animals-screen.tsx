"use client"

import Link from "next/link"
import { useState } from "react"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { PageHeader } from "@/components/shared/page-header"
import { SegmentedControl } from "@/components/shared/segmented-control"
import { entryHref } from "@/features/entry/links"
import { formatNumber } from "@/lib/format"
import { FarmPlan } from "./farm-plan"
import { LotsTable } from "./lots-table"
import { useLots } from "./queries"

type View = "plan" | "list"

const VIEWS = [
  { value: "plan" as const, label: "Plan", icon: "table-cells-large" },
  { value: "list" as const, label: "Liste", icon: "list" },
]

export function AnimalsScreen() {
  const [view, setView] = useState<View>("plan")
  const lots = useLots().data ?? []
  const animals = lots.reduce((total, lot) => total + lot.headcount, 0)

  return (
    <div className="flex flex-col gap-5">
      <PageHeader
        title="Animaux"
        description={`${lots.length} lots actifs · ${formatNumber(animals)} animaux · ${lots.length} loges occupées sur 24`}
        actions={
          <>
            <SegmentedControl label="Affichage" options={VIEWS} value={view} onValueChange={setView} />
            <Button asChild>
              <Link href={entryHref("new-lot")}>
                <Icon name="plus" />
                Créer un lot
              </Link>
            </Button>
          </>
        }
      />
      {view === "plan" ? <FarmPlan lots={lots} /> : <LotsTable lots={lots} />}
    </div>
  )
}
