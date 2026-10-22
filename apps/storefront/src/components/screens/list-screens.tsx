"use client";

import Link from "next/link";
import { useCurrency } from "@/hooks/use-farm";
import { useListings } from "@/hooks/use-listings";
import { useLots } from "@/hooks/use-lots";
import { useSows } from "@/hooks/use-sows";
import { Badge } from "@/components/ui/badge";
import { Icon } from "@/components/ui/icon";
import { formatKg, formatMoney, formatShortDate } from "@/lib/format";
import { expectedFarrowing, mean } from "@/lib/herd";

// Tab-level lists. Design covers detail screens only; these follow the home card language.

function ListPage({ title, children }: { title: string; children: React.ReactNode }) {
  return (
    <div className="flex flex-col gap-4 px-4 pt-[max(1.5rem,env(safe-area-inset-top))]">
      <h1 className="text-2xl font-bold">{title}</h1>
      <ul className="flex flex-col divide-y divide-border rounded-[20px] border border-border bg-card">{children}</ul>
    </div>
  );
}

function Row({ href, icon, title, detail, aside }: { href: string; icon: string; title: string; detail: string; aside?: React.ReactNode }) {
  return (
    <li>
      <Link href={href} className="flex items-center gap-3 px-4 py-3.5 text-foreground no-underline">
        <span className="grid size-9 flex-none place-items-center rounded-[10px] bg-muted text-ring">
          <Icon name={icon} />
        </span>
        <span className="flex flex-1 flex-col gap-0.5">
          <span className="text-sm font-semibold">{title}</span>
          <span className="text-xs text-muted-foreground">{detail}</span>
        </span>
        {aside}
        <Icon name="chevron-right" className="text-xs text-muted-foreground" />
      </Link>
    </li>
  );
}

export function LotsListScreen() {
  const { data: lots = [] } = useLots();
  return (
    <ListPage title="Mes lots">
      {lots.map((lot) => (
        <Row
          key={lot.id}
          href={`/lots/${lot.id}`}
          icon="layer-group"
          title={`Lot ${lot.code}`}
          detail={`${lot.phase} · ${lot.currentCount} porcs · ${formatKg(mean(lot.weights))}`}
        />
      ))}
    </ListPage>
  );
}

export function SowsListScreen() {
  const { data: sows = [] } = useSows();
  return (
    <ListPage title="Reproduction">
      {sows.map((sow) => (
        <Row
          key={sow.id}
          href={`/repro/${sow.id}`}
          icon="venus-mars"
          title={`Truie ${sow.code}`}
          detail={`Mise bas prévue ${formatShortDate(expectedFarrowing(new Date(sow.serviceDate)))}`}
          aside={<Badge tone="success">Gestante</Badge>}
        />
      ))}
    </ListPage>
  );
}

export function SalesListScreen() {
  const currency = useCurrency();
  const { data: listings = [] } = useListings();
  return (
    <ListPage title="Ventes">
      {listings.map((listing) => (
        <Row
          key={listing.lotId}
          href={`/lots/${listing.lotId}/sell`}
          icon="store"
          title={listing.title}
          detail={`${formatMoney(listing.priceXofPerKg, currency)} / kg · ${listing.available} disponibles`}
          aside={listing.published ? <Badge tone="success">Publiée</Badge> : <Badge>Brouillon</Badge>}
        />
      ))}
    </ListPage>
  );
}
