import { toast } from "sonner"
import { Icon } from "@/components/icon"
import { Badge } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Card, CardHeader } from "@/components/ui/card"
import { Notice } from "@/components/shared/notice"
import { Pill } from "@/components/shared/pill"
import { useActiveFarm } from "@/features/farms/active-farm"
import { usePdfExport } from "@/hooks/use-pdf-export"
import { formatMoney, formatNumber } from "@/lib/format"
import type { Ration } from "./solve-ration"

const LINE_COLORS = ["bg-chart-1", "bg-chart-4", "bg-chart-5", "bg-avatar-neutral"]

type RationResultProps = { ration: Ration; name: string; totalKg: number }

export function RationResult({ ration, name, totalKg }: RationResultProps) {
  const { farm } = useActiveFarm()
  const exportPdf = usePdfExport()
  const { nutrients, requirement } = ration
  const cheaper = ration.costVsStandard < 0

  const checks = [
    {
      name: "Protéines brutes",
      need: `≥ ${formatNumber(requirement.protein, 1)} %`,
      value: `${formatNumber(nutrients.protein, 1)} %`,
      tone: nutrients.protein >= requirement.protein - 0.05 ? "success" : "danger",
    },
    {
      name: "Énergie métabolisable",
      need: `≥ ${formatNumber(requirement.energy)} kcal/kg`,
      value: `${formatNumber(nutrients.energy)} kcal`,
      tone: nutrients.energy >= requirement.energy ? "success" : "warning",
    },
    {
      name: "Lysine",
      need: `≥ ${formatNumber(requirement.lysine, 2)} %`,
      value: `${formatNumber(nutrients.lysine, 2)} %`,
      tone: nutrients.lysine >= requirement.lysine ? "success" : "warning",
    },
    { name: "Somme des incorporations", need: "= 100 %", value: "100 %", tone: "success" },
  ] as const

  return (
    <>
      <section className="flex flex-wrap items-end gap-6 rounded-xl bg-hero p-6 text-hero-foreground">
        <div className="flex min-w-50 flex-1 flex-col gap-1">
          <span className="text-body font-semibold">{name}</span>
          <span className="font-heading text-figure font-bold tabular-nums">{formatMoney(ration.costPerKg, farm.currency)}</span>
          <span className="text-caption text-hero-muted">
            par kg d’aliment · {formatMoney(ration.costPerKg * totalKg, farm.currency)} pour {formatNumber(totalKg)} kg
          </span>
        </div>
        <Pill>
          {cheaper ? "−" : "+"}
          {formatNumber(Math.abs(ration.costVsStandard) * 100, 1)} % vs formule standard
        </Pill>
      </section>

      <div className="grid grid-cols-[repeat(auto-fit,minmax(280px,1fr))] gap-4">
        <Card>
          <CardHeader title="Incorporations" />
          <div className="flex flex-col gap-3">
            {ration.lines.map((line, i) => (
              <div key={line.ingredient.key} className="flex flex-col gap-1.5">
                <div className="flex justify-between text-sm">
                  <span className="font-semibold">{line.ingredient.name}</span>
                  <span className="tabular-nums">
                    {formatNumber(line.share * 100, 1)} % · {formatNumber(line.share * totalKg, 1)} kg
                  </span>
                </div>
                <div className="h-1.5 overflow-hidden rounded-full bg-muted">
                  <div className={`h-full ${LINE_COLORS[i]}`} style={{ width: `${line.share * 100}%` }} />
                </div>
              </div>
            ))}
          </div>
        </Card>
        <Card>
          <CardHeader title="Besoins de la phase" />
          <div className="flex flex-col">
            {checks.map((check) => (
              <div key={check.name} className="flex items-center justify-between gap-3 border-t py-2.5">
                <div className="flex flex-col">
                  <span className="text-body font-semibold">{check.name}</span>
                  <span className="text-xs text-muted-foreground">Besoin {check.need}</span>
                </div>
                <Badge tone={check.tone}>{check.value}</Badge>
              </div>
            ))}
          </div>
        </Card>
      </div>

      {nutrients.lysine < requirement.lysine && (
        <Notice tone="warning" title="Lysine insuffisante">
          La lysine atteint {formatNumber(nutrients.lysine, 2)} % pour un besoin de {formatNumber(requirement.lysine, 2)} %. Ajoutez la
          farine de poisson ou de la L-lysine HCl.
        </Notice>
      )}

      <div className="flex flex-wrap justify-end gap-2">
        <Button variant="outline" onClick={exportPdf}>
          <Icon name="file-arrow-down" />
          Fiche ration PDF
        </Button>
        {/* TODO(#4.6): POST /formulas */}
        <Button onClick={() => toast.success("Formule enregistrée", { description: `${name} est disponible dans Mes formules.` })}>
          <Icon name="floppy-disk" />
          Enregistrer la ration
        </Button>
      </div>
    </>
  )
}
