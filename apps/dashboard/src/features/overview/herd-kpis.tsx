import { Badge } from "@/components/ui/badge"
import { HeroCard } from "@/components/shared/hero-card"
import { StatCard } from "@/components/shared/stat-card"
import { HERD_TARGETS, type HerdStats } from "@/features/lots/herd-stats"
import { formatNumber } from "@/lib/format"

type HerdKpisProps = { stats: HerdStats; lotCount: number }

export function HerdKpis({ stats, lotCount }: HerdKpisProps) {
  const gainOk = stats.dailyGain >= HERD_TARGETS.dailyGain
  const fcrOk = stats.feedConversion <= HERD_TARGETS.feedConversion
  const mortalityOk = stats.mortalityRate < HERD_TARGETS.mortalityRate

  return (
    <div className="grid grid-cols-[repeat(auto-fit,minmax(220px,1fr))] gap-4">
      <HeroCard
        icon="layer-group"
        label="Effectif présent"
        value={formatNumber(stats.headcount)}
        change="+2,1 % sur 30 j"
        caption={`${lotCount} lots actifs · 3 bâtiments · 48 truies`}
        link={{ href: "/animals", label: "Voir les lots" }}
      />
      <StatCard
        icon="arrow-trend-up"
        label="Croissance (GMQ)"
        value={formatNumber(stats.dailyGain)}
        unit="g/j"
        link={{ href: "/animals", label: "Ouvrir les lots" }}
        footer={
          <Badge tone={gainOk ? "success" : "warning"} size="sm">
            {gainOk ? "Bon" : "À surveiller"} · objectif ≥ {HERD_TARGETS.dailyGain} g/j
          </Badge>
        }
      />
      <StatCard
        icon="wheat-awn"
        label="Indice de conso. (IC)"
        value={formatNumber(stats.feedConversion, 2)}
        link={{ href: "/feed", label: "Ouvrir l’alimentation" }}
        footer={
          <Badge tone={fcrOk ? "success" : "warning"} size="sm">
            {fcrOk ? "Bon" : "À surveiller"} · objectif ≤ {formatNumber(HERD_TARGETS.feedConversion, 2)}
          </Badge>
        }
      />
      <StatCard
        icon="skull-crossbones"
        label="Mortalité des lots"
        value={formatNumber(stats.mortalityRate * 100, 1)}
        unit="%"
        link={{ href: "/animals", label: "Ouvrir les lots" }}
        footer={
          <Badge tone={mortalityOk ? "success" : "danger"} size="sm">
            {mortalityOk ? "Bon" : "Problème"} · objectif &lt; 3 %
          </Badge>
        }
      />
    </div>
  )
}
