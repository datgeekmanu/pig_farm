"use client";

import { ScreenLoading } from "@/components/app/screen-loading";
import { useSow } from "@/hooks/use-sows";
import { GestationRing } from "@/components/app/gestation-ring";
import { BackButton } from "@/components/app/round-button";
import { Badge } from "@/components/ui/badge";
import { Button } from "@/components/ui/button";
import { Icon } from "@/components/ui/icon";
import { ImageSlot } from "@/components/ui/image-slot";
import { formatDayMonth, formatNumber, formatShortDate } from "@/lib/format";
import { daysBetween, DEFAULT_GESTATION_DAYS, expectedFarrowing } from "@/lib/herd";
import { cn } from "@/lib/utils";

const STATUS_LABELS = { gestante: "Gestante", vide: "Vide", allaitante: "Allaitante" } as const;

function countdown(days: number) {
  if (days <= 0) return "Aujourd'hui";
  if (days === 1) return "Demain";
  return `Dans ${days} jours`;
}

export function SowScreen({ id }: { id: string }) {
  const { data: sow, error } = useSow(id);

  if (error) return <p className="p-6 text-danger">{error.message}</p>;
  if (!sow) return <ScreenLoading />;

  const serviceDate = new Date(sow.serviceDate);
  const gestationDay = daysBetween(serviceDate, new Date());
  const dueDate = expectedFarrowing(serviceDate, DEFAULT_GESTATION_DAYS);
  const daysLeft = daysBetween(new Date(), dueDate);

  return (
    <>
      <main className="flex-1 pb-28">
        <div className="flex flex-col gap-1.5 px-5 pt-[max(1rem,env(safe-area-inset-top))]">
          <div className="flex h-11 items-center justify-between">
            <BackButton fallbackHref="/repro" />
            {/* TODO(#3.1): sow actions menu */}
            <Icon name="ellipsis-vertical" className="text-lg" />
          </div>
          <div className="mt-1 flex items-center gap-3.5">
            <div className="size-16 flex-none">
              <ImageSlot src={sow.photo} alt={`Truie ${sow.code}`} placeholder="Truie" round />
            </div>
            <div className="flex flex-col gap-1">
              <h1 className="text-2xl font-bold">Truie {sow.code}</h1>
              <span className="flex items-center gap-2.5">
                <Badge tone="success">{STATUS_LABELS[sow.status]}</Badge>
                <span className="text-meta text-muted-foreground">
                  {sow.breed} · portée n°{sow.parity}
                </span>
              </span>
            </div>
          </div>
        </div>

        <section className="mx-4 mt-5 flex items-center gap-4.5 rounded-xl bg-hero p-5 text-hero-foreground">
          <GestationRing day={gestationDay} total={DEFAULT_GESTATION_DAYS} />
          <div className="flex flex-col gap-2">
            <span className="text-meta font-semibold text-hero-muted">Mise bas prévue</span>
            <span className="font-heading text-figure leading-tight font-bold">{formatShortDate(dueDate)}</span>
            <span className="self-start rounded-full bg-highlight px-2.5 py-1 text-xs font-bold text-highlight-foreground">
              {countdown(daysLeft)}
            </span>
          </div>
        </section>

        <section className="flex flex-col gap-3.5 px-5 pt-6">
          <h2 className="text-lg font-bold">Cycle en cours</h2>
          <ol className="flex flex-col">
            {sow.cycle.map((event, index) => {
              const last = index === sow.cycle.length - 1;
              return (
                <li key={event.key} className="flex gap-3.5">
                  <div className="flex w-7 flex-col items-center">
                    <span
                      className={cn(
                        "grid size-7 place-items-center rounded-full border-[1.5px] text-xs",
                        event.done
                          ? "border-primary bg-primary text-primary-foreground"
                          : "border-foreground bg-card text-foreground",
                      )}
                    >
                      <Icon name={event.done ? "check" : "baby-carriage"} />
                    </span>
                    {!last && <span className="min-h-5.5 w-0.5 flex-1 bg-border" />}
                  </div>
                  <div className="flex flex-1 justify-between gap-2.5 pt-0.5 pb-4.5">
                    <div className="flex flex-col gap-0.5">
                      <span className="text-lead font-semibold">{event.title}</span>
                      <span className="text-meta text-muted-foreground">{event.detail}</span>
                    </div>
                    <span className="text-meta font-semibold whitespace-nowrap text-muted-foreground tabular-nums">
                      {formatDayMonth(new Date(event.date))}
                    </span>
                  </div>
                </li>
              );
            })}
          </ol>

          <dl className="grid grid-cols-3 gap-2">
            {[
              { label: "Nés vifs / portée", value: formatNumber(sow.bornAlivePerLitter) },
              { label: "Survie sevrage", value: `${sow.weaningSurvivalPct} %` },
              { label: "Verrat", value: sow.boar },
            ].map((item) => (
              <div key={item.label} className="flex flex-col-reverse gap-0.5 rounded-lg border border-border bg-card p-3">
                <dt className="text-xs leading-tight text-muted-foreground">{item.label}</dt>
                <dd className="font-heading text-lg font-bold tabular-nums">{item.value}</dd>
              </div>
            ))}
          </dl>
        </section>
      </main>

      <div className="fixed inset-x-0 bottom-0 z-10 border-t border-border bg-background px-5 pt-3 pb-[max(1.875rem,env(safe-area-inset-bottom))]">
        {/* TODO(#3.4): farrowing form */}
        <Button size="lg" fullWidth icon="baby-carriage">
          Déclarer la mise bas
        </Button>
      </div>
    </>
  );
}
