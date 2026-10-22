"use client";

import Link from "next/link";
import { useFarmSummary, useFarmTasks } from "@/hooks/use-farm";
import { useLots } from "@/hooks/use-lots";
import { Icon } from "@/components/ui/icon";
import { ImageSlot } from "@/components/ui/image-slot";
import { formatInteger, formatKg, formatLongDate, formatNumber, formatPercent } from "@/lib/format";
import { mean } from "@/lib/herd";
import type { TaskTone } from "@/lib/models";
import { cn } from "@/lib/utils";

// Progress bars on lot cards run toward slaughter weight.
const TARGET_WEIGHT_KG = 120;

const TASK_TONES: Record<TaskTone, { icon: string; detail: string }> = {
  danger: { icon: "text-danger", detail: "text-danger" },
  warning: { icon: "text-warning", detail: "text-warning" },
  neutral: { icon: "text-warning", detail: "text-muted-foreground" },
};

const LOT_BAR_COLORS = ["bg-chart-4", "bg-chart-5"];

export function HomeScreen() {
  const { data: farm } = useFarmSummary();
  const { data: tasks = [] } = useFarmTasks();
  const { data: lots = [] } = useLots();

  return (
    <>
      <header className="rounded-b-2xl bg-sidebar px-5 pt-[max(1rem,env(safe-area-inset-top))] pb-23 text-sidebar-foreground">
        <div className="mt-2.5 flex items-center justify-between">
          <div className="flex flex-col gap-1">
            <h1 className="text-figure font-bold">Bonjour {farm?.user.firstName}</h1>
            {/* Data resolves on the client only, so `new Date()` never runs during prerender. */}
            {farm && <span className="text-meta text-sidebar-muted">{formatLongDate(new Date())}</span>}
          </div>
          <div className="relative">
            <span className="grid size-11 place-items-center rounded-full bg-hero font-heading text-lead font-bold">
              {farm?.user.initials}
            </span>
            {!!farm?.user.unreadNotifications && (
              <span
                aria-label={`${farm.user.unreadNotifications} notifications`}
                className="absolute -top-0.5 -right-0.5 grid size-[18px] place-items-center rounded-full border-2 border-sidebar bg-highlight text-[10px] font-bold text-highlight-foreground"
              >
                {farm.user.unreadNotifications}
              </span>
            )}
          </div>
        </div>
        <div className="mt-4 flex gap-2">
          {/* TODO(#1.4): farm switcher menu */}
          <button
            type="button"
            className="flex h-11 flex-1 items-center gap-2.5 rounded-xl bg-sidebar-tile px-3.5 text-left text-sm font-semibold"
          >
            <Icon name="warehouse" className="text-sidebar-muted" />
            <span className="flex-1">{farm?.name}</span>
            <Icon name="chevron-down" className="text-2xs" />
          </button>
          <div className="flex h-11 items-center gap-2 rounded-xl bg-sidebar-tile px-3 text-xs font-semibold text-sidebar-muted">
            <span className="size-2 rounded-full bg-switch-on" />
            Synchronisé
          </div>
        </div>
      </header>

      {farm && (
        <section className="mx-4 -mt-19 flex flex-col gap-4 rounded-xl bg-hero p-5 text-hero-foreground">
          <div className="flex items-start justify-between">
            <div className="flex flex-col gap-1">
              <span className="text-meta font-semibold text-hero-muted">Effectif total</span>
              <span className="font-heading text-4xl leading-none font-bold tabular-nums">
                {formatInteger(farm.herd.total)} <span className="text-base font-semibold">porcs</span>
              </span>
            </div>
            <span className="rounded-full bg-highlight px-2.5 py-1 text-xs font-bold text-highlight-foreground">
              +{farm.herd.weeklyChange} cette sem.
            </span>
          </div>
          <div className="grid grid-cols-3 gap-2">
            {[
              { label: "GMQ", value: `${farm.herd.adgGramsPerDay} g/j` },
              { label: "Mortalité", value: formatPercent(farm.herd.mortalityPct) },
              { label: "IC", value: formatNumber(farm.herd.feedConversion, 2) },
            ].map((kpi) => (
              <div key={kpi.label} className="flex flex-col gap-0.5 rounded-xl bg-white/12 px-3 py-2.5">
                <span className="text-2xs font-semibold text-hero-muted">{kpi.label}</span>
                <span className="font-heading text-base font-bold tabular-nums">{kpi.value}</span>
              </div>
            ))}
          </div>
        </section>
      )}

      <section className="flex flex-col gap-3 px-4 pt-6">
        <div className="flex items-baseline justify-between">
          <h2 className="text-lg font-bold">À faire aujourd&apos;hui</h2>
          <Link href="/" className="text-meta font-semibold text-ring no-underline">
            Voir tout
          </Link>
        </div>
        <ul className="flex flex-col divide-y divide-border rounded-[20px] border border-border bg-card">
          {tasks.map((task) => (
            <li key={task.id}>
              <Link href={task.href} className="flex items-center gap-3 px-4 py-3.5 text-foreground no-underline">
                <span className={cn("grid size-9 flex-none place-items-center rounded-[10px] bg-muted", TASK_TONES[task.tone].icon)}>
                  <Icon name={task.icon} />
                </span>
                <span className="flex flex-1 flex-col gap-0.5">
                  <span className="text-sm font-semibold">{task.title}</span>
                  <span className={cn("text-xs font-semibold", TASK_TONES[task.tone].detail)}>{task.detail}</span>
                </span>
                <Icon name="chevron-right" className="text-xs text-muted-foreground" />
              </Link>
            </li>
          ))}
        </ul>
      </section>

      <section className="flex flex-col gap-3 pt-6 pl-4">
        <div className="flex items-baseline justify-between pr-4">
          <h2 className="text-lg font-bold">Mes lots</h2>
          <Link href="/lots" className="text-meta font-semibold text-ring no-underline">
            Voir tout
          </Link>
        </div>
        <div className="flex snap-x gap-3 overflow-x-auto overscroll-x-contain pr-4 pb-1">
          {lots.map((lot, index) => {
            const averageKg = mean(lot.weights);
            return (
              <Link
                key={lot.id}
                href={`/lots/${lot.id}`}
                className="relative h-55 w-60 flex-none snap-start overflow-hidden rounded-xl bg-muted text-foreground no-underline"
              >
                <ImageSlot src={lot.photo} alt={`Lot ${lot.code}`} placeholder={`Photo du lot ${lot.code}`} />
                <div className="absolute inset-x-2 bottom-2 flex flex-col gap-2 rounded-[18px] bg-card px-3.5 py-3">
                  <div className="flex items-baseline justify-between">
                    <span className="font-heading text-lead font-bold">Lot {lot.code}</span>
                    <span className="text-xs font-semibold text-muted-foreground tabular-nums">{lot.currentCount} porcs</span>
                  </div>
                  <div className="flex items-center gap-2">
                    <div className="h-1.5 flex-1 rounded-full bg-muted">
                      <div
                        className={cn("h-full rounded-full", LOT_BAR_COLORS[index % LOT_BAR_COLORS.length])}
                        style={{ width: `${Math.min((averageKg / TARGET_WEIGHT_KG) * 100, 100)}%` }}
                      />
                    </div>
                    <span className="text-xs font-semibold tabular-nums">{formatKg(averageKg)}</span>
                  </div>
                </div>
              </Link>
            );
          })}
        </div>
      </section>
    </>
  );
}
