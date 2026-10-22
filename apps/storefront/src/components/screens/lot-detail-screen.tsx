"use client";

import { ScreenLoading } from "@/components/app/screen-loading";
import Link from "next/link";
import { useLot } from "@/hooks/use-lots";
import { GrowthChart } from "@/components/app/growth-chart";
import { BackButton, RoundLink } from "@/components/app/round-button";
import { Stat } from "@/components/app/stat";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Icon } from "@/components/ui/icon";
import { ImageSlot } from "@/components/ui/image-slot";
import { formatDayMonth, formatKg, formatNumber, formatPercent } from "@/lib/format";
import { coefficientOfVariation, daysBetween, HOMOGENEOUS_CV, mean } from "@/lib/herd";
import { ifipReferenceKg } from "@/lib/ifip";

export function LotDetailScreen({ id }: { id: string }) {
  const { data: lot, error } = useLot(id);

  if (error) return <p className="p-6 text-danger">{error.message}</p>;
  if (!lot) return <ScreenLoading />;

  const startedOn = new Date(lot.startedOn);
  const week = Math.floor(daysBetween(startedOn, new Date()) / 7);
  const averageKg = mean(lot.weights);
  const cv = coefficientOfVariation(lot.weights);
  const homogeneous = cv < HOMOGENEOUS_CV;
  const vsIfip = averageKg - ifipReferenceKg(week);
  const points = lot.history.map((h) => ({ week: daysBetween(startedOn, new Date(h.date)) / 7, kg: h.averageKg }));

  return (
    <>
      <main className="flex-1 bg-card pb-28">
        <div className="relative h-75 bg-muted">
          <ImageSlot src={lot.photo} alt={`Lot ${lot.code}`} placeholder={`Photo du lot ${lot.code} dans sa loge`} />
          <div className="absolute inset-x-5 top-[max(1rem,env(safe-area-inset-top))] flex justify-between">
            <BackButton fallbackHref="/lots" />
            <RoundLink href={`/lots/${lot.id}/sell`} icon="ellipsis-vertical" label="Plus d'actions" />
          </div>
        </div>

        <div className="relative -mt-8 flex flex-col gap-4.5 rounded-t-2xl bg-card px-5 pt-6">
          <div className="flex flex-col gap-1.5">
            <Badge outline>
              {lot.phase} · {lot.location}
            </Badge>
            <h1 className="text-title font-bold">Lot {lot.code}</h1>
            <p className="text-sm text-muted-foreground">
              Mis en lot le {formatDayMonth(startedOn)} · semaine {week} · {lot.initialCount} porcelets
            </p>
          </div>

          <div className="grid grid-cols-2 gap-x-3 gap-y-3.5">
            <Stat icon="layer-group" value={`${lot.currentCount} / ${lot.initialCount}`} label="Effectif" />
            <Stat icon="weight-scale" value={formatKg(averageKg)} label="Poids moyen" />
            <Stat icon="arrow-trend-up" value={`${lot.adgGramsPerDay} g/j`} label="GMQ" />
            <Stat
              icon={homogeneous ? "circle-check" : "triangle-exclamation"}
              tone={homogeneous ? "success" : "warning"}
              value={`CV ${formatPercent(cv)}`}
              label={homogeneous ? "Homogène" : "Hétérogène"}
            />
          </div>

          <section className="flex flex-col gap-2.5 rounded-[20px] border border-border p-4">
            <div className="flex items-baseline justify-between">
              <h2 className="text-lead font-bold">Croissance vs IFIP</h2>
              <span className={vsIfip >= 0 ? "text-xs font-semibold text-success" : "text-xs font-semibold text-warning"}>
                <Icon name={vsIfip >= 0 ? "arrow-trend-up" : "arrow-trend-down"} /> {vsIfip >= 0 ? "+" : ""}
                {formatNumber(vsIfip)} kg
              </span>
            </div>
            {lot.history.length > 1 ? (
              <GrowthChart points={points} weeks={Math.max(week + 4, 16)} />
            ) : (
              <p className="text-sm text-muted-foreground">Ajoutez des pesées pour voir la courbe de croissance.</p>
            )}
          </section>

          {lot.vaccineDueToday && (
            <Link
              href="/"
              className="flex h-13 items-center gap-2.5 rounded-[14px] border border-border px-3.5 text-foreground no-underline"
            >
              <Icon name="circle-exclamation" className="text-danger" />
              <span className="flex-1 text-sm font-semibold">Rappel vaccin aujourd&apos;hui</span>
              <Icon name="arrow-right" className="text-muted-foreground" />
            </Link>
          )}

          <section className="flex flex-col gap-2.5">
            <h2 className="text-lead font-bold">Soignant du jour</h2>
            <div className="flex items-center gap-3 rounded-[18px] bg-sidebar p-3 text-white">
              <span className="grid size-11 place-items-center rounded-full bg-hero font-heading text-sm font-bold">
                {lot.caregiver.initials}
              </span>
              <div className="flex flex-1 flex-col gap-0.5">
                <span className="font-heading text-lead font-bold">{lot.caregiver.name}</span>
                <span className="text-xs text-sidebar-muted">{lot.caregiver.shift}</span>
              </div>
              <a
                href={`tel:${lot.caregiver.phone}`}
                aria-label={`Appeler ${lot.caregiver.name}`}
                className="grid size-11 place-items-center rounded-full bg-sidebar-tile text-switch-on"
              >
                <Icon name="phone" />
              </a>
            </div>
          </section>
        </div>
      </main>

      <div className="fixed inset-x-0 bottom-0 z-10 flex gap-2.5 border-t border-border bg-card px-5 pt-3 pb-[max(1.875rem,env(safe-area-inset-bottom))]">
        {/* TODO(#2.5): mortality form */}
        <Button variant="outline" size="lg" iconOnly icon="skull-crossbones" aria-label="Déclarer une mortalité" />
        <div className="flex-1">
          <Button asChild size="lg" fullWidth>
            <Link href={`/lots/${lot.id}/weigh`} className="no-underline">
              <Icon name="weight-scale" />
              Nouvelle pesée
            </Link>
          </Button>
        </div>
      </div>
    </>
  );
}
