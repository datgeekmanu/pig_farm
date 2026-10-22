"use client"

import Link from "next/link"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { PageHeader } from "@/components/shared/page-header"
import { entryHref } from "@/features/entry/links"
import { healthApi } from "./api"
import { useStock } from "./queries"
import { StockList } from "./stock-list"
import { TreatmentList } from "./treatment-list"

export function HealthScreen() {
  const stock = useStock().data ?? []

  return (
    <div className="flex flex-col gap-6">
      <PageHeader
        title="Santé et stocks"
        description="Traitements, prophylaxie et niveaux de stock"
        actions={
          <>
            <Button variant="outline" asChild>
              <Link href={entryHref("stock-in")}>
                <Icon name="box" />
                Saisir une entrée de stock
              </Link>
            </Button>
            <Button asChild>
              <Link href={entryHref("treatment")}>
                <Icon name="syringe" />
                Saisir un traitement
              </Link>
            </Button>
          </>
        }
      />
      <div className="grid grid-cols-[repeat(auto-fit,minmax(min(100%,420px),1fr))] items-start gap-6">
        <StockList items={stock} />
        <TreatmentList treatments={healthApi.treatments()} />
      </div>
    </div>
  )
}
