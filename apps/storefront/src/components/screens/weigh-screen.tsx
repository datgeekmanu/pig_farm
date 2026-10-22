"use client";

import { ScreenLoading } from "@/components/app/screen-loading";
import { useState } from "react";
import { toast } from "sonner";
import { useLot, useRecordWeight } from "@/hooks/use-lots";
import { Button } from "@/components/ui/button";
import { Icon } from "@/components/ui/icon";
import { useRouter } from "next/navigation";
import { daysBetween, coefficientOfVariation, mean } from "@/lib/herd";
import { formatKg, formatNumber, formatPercent } from "@/lib/format";
import { ifipReferenceKg } from "@/lib/ifip";

const KEYS = ["1", "2", "3", "4", "5", "6", "7", "8", "9", ",", "0", "⌫"];

// Up to 3 integer digits and 1 decimal: 0 – 999,9 kg.
export function nextEntry(entry: string, key: string) {
  if (key === "⌫") return entry.slice(0, -1);
  if (key === ",") return entry.includes(",") ? entry : `${entry || "0"},`;
  const [integer, decimal] = entry.split(",");
  if (decimal !== undefined ? decimal.length >= 1 : integer.length >= 3) return entry;
  return entry + key;
}

export function WeighScreen({ id }: { id: string }) {
  const router = useRouter();
  const { data: lot } = useLot(id);
  const recordWeight = useRecordWeight(id);
  const [entry, setEntry] = useState("");

  if (!lot) return <ScreenLoading />;

  const value = Number.parseFloat(entry.replace(",", "."));
  const week = Math.floor(daysBetween(new Date(lot.startedOn), new Date()) / 7);

  function save() {
    if (!(value > 0)) return;
    recordWeight.mutate(value, {
      onSuccess: () => {
        setEntry("");
        toast.success(`Pesée enregistrée : ${formatKg(value)}`);
      },
    });
  }

  return (
    <main className="dark flex min-h-dvh flex-col bg-sidebar text-sidebar-foreground">
      <div className="flex flex-col gap-1.5 px-6 pt-[max(1rem,env(safe-area-inset-top))]">
        <div className="flex items-center justify-between">
          <button type="button" aria-label="Retour" onClick={() => router.back()} className="-ml-2 grid size-11 place-items-center text-lg">
            <Icon name="arrow-left" />
          </button>
          {/* TODO: Web Bluetooth scale pairing */}
          <span className="flex items-center gap-1.5 text-xs font-semibold text-sidebar-muted">
            <span className="size-2 rounded-full bg-switch-on" />
            Balance connectée
          </span>
        </div>
        <h1 className="mt-2.5 text-title font-bold">Pesée — Lot {lot.code}</h1>
        <p className="text-sm text-sidebar-muted">
          Pesée individuelle · {lot.weights.length} sur {lot.currentCount} animaux
        </p>
      </div>

      <output aria-live="polite" className="flex items-baseline gap-2 px-6 pt-5">
        <span className="font-heading text-readout font-bold tracking-tight tabular-nums">{entry || "0"}</span>
        <span className="font-heading text-figure font-semibold text-sidebar-muted">kg</span>
      </output>

      <dl className="grid grid-cols-3 gap-2 px-6 pt-5">
        {[
          { label: "Poids moyen", value: formatKg(mean(lot.weights)) },
          { label: "Homogénéité", value: `CV ${formatPercent(coefficientOfVariation(lot.weights))}` },
          { label: "Réf. IFIP", value: formatKg(ifipReferenceKg(week)) },
        ].map((item) => (
          <div key={item.label} className="flex flex-col gap-0.5">
            <dt className="text-xs font-semibold text-sidebar-muted">{item.label}</dt>
            <dd className="font-heading text-lg font-bold tabular-nums">{item.value}</dd>
          </div>
        ))}
      </dl>

      <ul aria-label="Dernières pesées" className="flex gap-1.5 overflow-hidden px-6 pt-4">
        {lot.weights
          .slice(-5)
          .reverse()
          .map((w, i) => (
            <li key={`${i}-${w}`} className="flex-none rounded-full bg-sidebar-tile px-2.5 py-1.5 text-xs font-semibold tabular-nums">
              {formatNumber(w)}
            </li>
          ))}
      </ul>

      <div className="mt-auto grid grid-cols-3 gap-2 px-4 pt-6 pb-3.5">
        {KEYS.map((key) => (
          <button
            key={key}
            type="button"
            onClick={() => setEntry((e) => nextEntry(e, key))}
            aria-label={key === "⌫" ? "Effacer" : key === "," ? "Virgule" : key}
            className="h-14.5 touch-manipulation rounded-lg bg-sidebar-tile font-heading text-figure font-semibold tabular-nums select-none active:bg-sidebar-tile-hover"
          >
            {key}
          </button>
        ))}
      </div>

      <div className="px-4 pb-[max(2.125rem,env(safe-area-inset-bottom))]">
        <Button size="lg" fullWidth icon="check" disabled={!(value > 0)} loading={recordWeight.isPending} onClick={save}>
          Enregistrer la pesée
        </Button>
      </div>
    </main>
  );
}
