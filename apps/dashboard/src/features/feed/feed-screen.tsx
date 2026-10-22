"use client"

import { useState } from "react"
import { EmptyState } from "@/components/shared/empty-state"
import { Notice } from "@/components/shared/notice"
import { PageHeader } from "@/components/shared/page-header"
import { SegmentedControl } from "@/components/shared/segmented-control"
import { parseNumber } from "@/lib/format"
import { INGREDIENTS } from "./ingredients"
import { RationForm, type RationRequest } from "./ration-form"
import { RationResult } from "./ration-result"
import { solveRation, type Infeasible, type Ration } from "./solve-ration"

// TODO(#4.3, #4.5): manual formulation and analysis modes.
const MODES = [
  { value: "manual" as const, label: "Manuel", disabled: true },
  { value: "least-cost" as const, label: "Économique" },
  { value: "analysis" as const, label: "Analyse", disabled: true },
]

type Outcome = { request: RationRequest; result: Ration | Infeasible }

export function FeedScreen() {
  const [outcome, setOutcome] = useState<Outcome | null>(null)

  const solve = (request: RationRequest) => {
    const pool = INGREDIENTS.filter((ingredient) => request.pool.includes(ingredient.key))
    setOutcome({ request, result: solveRation(request.phase, pool) })
  }

  return (
    <div className="flex flex-col gap-6">
      <PageHeader
        title="Ration la moins chère"
        description="À partir de ce que vous avez en stock et des besoins de vos animaux"
        actions={<SegmentedControl label="Mode" options={MODES} value="least-cost" onValueChange={() => {}} />}
      />
      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,340px),1fr))] items-start gap-6">
        <RationForm onSolve={solve} />
        <div className="flex flex-col gap-4">
          {!outcome && <EmptyState icon="wheat-awn">Choisissez la phase et les ingrédients, puis lancez le calcul.</EmptyState>}
          {outcome && !outcome.result.ok && (
            <Notice tone="danger" title="Aucune formule possible avec ces ingrédients">
              {outcome.result.reason}
            </Notice>
          )}
          {outcome?.result.ok && (
            <RationResult ration={outcome.result} name={outcome.request.name} totalKg={parseNumber(outcome.request.totalKg)} />
          )}
        </div>
      </div>
    </div>
  )
}
