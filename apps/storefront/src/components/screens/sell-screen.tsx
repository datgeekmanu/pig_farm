"use client";

import { ScreenLoading } from "@/components/app/screen-loading";
import { useState } from "react";
import { toast } from "sonner";
import { useCurrency } from "@/hooks/use-farm";
import { useListing, usePublishListing } from "@/hooks/use-listings";
import { BackButton, RoundButton } from "@/components/app/round-button";
import { Stepper } from "@/components/app/stepper";
import { IconTile } from "@/components/app/stat";
import { Button } from "@/components/ui/button";
import { ImageSlot } from "@/components/ui/image-slot";
import { Switch } from "@/components/ui/switch";
import { formatMoney, formatNumber } from "@/lib/format";
import type { Listing } from "@/lib/models";

const PRICE_STEP_XOF = 50;
const MIN_PRICE_XOF = 500;
// Above this gap vs market, the delta turns into a warning.
const PRICE_WARNING_PCT = 8;

export function SellScreen({ lotId }: { lotId: string }) {
  const { data: listing } = useListing(lotId);
  if (!listing) return <ScreenLoading />;
  // Remount the form if the server copy changes.
  return <SellForm key={`${listing.lotId}-${listing.published}`} initial={listing} />;
}

function SellForm({ initial }: { initial: Listing }) {
  const currency = useCurrency();
  const publish = usePublishListing();
  const [price, setPrice] = useState(initial.priceXofPerKg);
  const [quantity, setQuantity] = useState(initial.quantity);
  const [visible, setVisible] = useState(initial.visible);

  const deltaPct = ((price - initial.marketXofPerKg) / initial.marketXofPerKg) * 100;
  const total = price * quantity * initial.averageKg;

  async function share() {
    const data = { title: initial.title, text: `${initial.title} · ${formatMoney(price, currency)} / kg`, url: window.location.href };
    if (navigator.share) await navigator.share(data).catch(() => undefined);
  }

  return (
    <>
      <main className="relative flex-1 bg-card">
        <div className="fixed inset-x-0 top-0 h-[330px] bg-muted">
          <ImageSlot src={initial.photo} alt={initial.title} placeholder="Photo des porcs charcutiers du lot" />
          <div className="absolute inset-x-5 top-[max(1rem,env(safe-area-inset-top))] flex justify-between">
            <BackButton fallbackHref="/sales" />
            <RoundButton icon="share-nodes" label="Partager" onClick={share} />
          </div>
        </div>

        <div className="relative mt-74 flex min-h-[calc(100dvh-18.5rem)] flex-col gap-4 rounded-t-2xl bg-card px-5 pt-5.5 pb-32">
          <div className="flex flex-col gap-1.5">
            <span className="self-start rounded-full bg-hero px-2.5 py-1 text-xs font-bold text-hero-foreground">
              {initial.category}
            </span>
            <h1 className="text-title font-bold">{initial.title}</h1>
            <p className="text-sm text-muted-foreground">
              {initial.available} porcs disponibles · {formatNumber(initial.averageKg, 0)} kg en moyenne
            </p>
          </div>

          <div className="flex items-center justify-between gap-3">
            <div className="flex items-center gap-2.5">
              <IconTile icon="tag" />
              <div className="flex flex-col">
                <span className="text-xs text-muted-foreground">Prix au kg vif</span>
                <span className="font-heading text-xl font-bold tabular-nums">{formatMoney(price, currency)}</span>
              </div>
            </div>
            <Stepper
              onDecrement={() => setPrice((p) => Math.max(MIN_PRICE_XOF, p - PRICE_STEP_XOF))}
              onIncrement={() => setPrice((p) => p + PRICE_STEP_XOF)}
              canDecrement={price > MIN_PRICE_XOF}
              decrementLabel="Baisser le prix"
              incrementLabel="Augmenter le prix"
            />
          </div>

          <div className="flex items-end justify-between rounded-lg border border-border px-3.5 py-3">
            <div className="flex flex-col gap-0.5">
              <span className="text-xs text-muted-foreground">Prix moyen du marché</span>
              <span className="font-heading text-lead font-bold tabular-nums">
                {formatMoney(initial.marketXofPerKg, currency)} / kg
              </span>
            </div>
            <span className={deltaPct > PRICE_WARNING_PCT ? "text-xs font-semibold text-warning" : "text-xs font-semibold text-muted-foreground"}>
              {deltaPct >= 0 ? "+" : ""}
              {formatNumber(deltaPct)} % vs marché
            </span>
          </div>

          <div className="flex items-center justify-between">
            <div className="flex flex-col">
              <span className="text-lead font-semibold">Nombre de porcs</span>
              <span className="text-xs text-muted-foreground">Retirés de l&apos;effectif à la vente</span>
            </div>
            <Stepper
              value={quantity}
              onDecrement={() => setQuantity((q) => Math.max(1, q - 1))}
              onIncrement={() => setQuantity((q) => Math.min(initial.available, q + 1))}
              canDecrement={quantity > 1}
              canIncrement={quantity < initial.available}
              decrementLabel="Moins"
              incrementLabel="Plus"
            />
          </div>

          <div className="flex items-baseline justify-between border-t border-border pt-3.5">
            <span className="text-sm font-semibold text-muted-foreground">Total estimé</span>
            <span className="font-heading text-figure font-bold tabular-nums">{formatMoney(total, currency)}</span>
          </div>

          <Switch
            checked={visible}
            onCheckedChange={setVisible}
            label="Visible sur la boutique"
            description="Les acheteurs peuvent commander depuis la boutique en ligne."
          />
        </div>
      </main>

      <div className="fixed inset-x-0 bottom-0 z-10 border-t border-border bg-card px-5 pt-3 pb-[max(1.875rem,env(safe-area-inset-bottom))]">
        <Button
          size="lg"
          fullWidth
          icon={initial.published ? "check" : "store"}
          loading={publish.isPending}
          onClick={() =>
            publish.mutate(
              { ...initial, priceXofPerKg: price, quantity, visible },
              { onSuccess: () => toast.success("Annonce publiée") },
            )
          }
        >
          {initial.published ? "Mettre à jour l'annonce" : "Publier l'annonce"}
        </Button>
      </div>
    </>
  );
}
