"use client";

import Link from "next/link";
import { Drawer } from "vaul";
import { Icon } from "@/components/ui/icon";

// TODO(#2.5, #5.2, #3.3, #5.1, #6.1): point the remaining actions at their forms once built.
const QUICK_ACTIONS = [
  { icon: "weight-scale", label: "Nouvelle pesée", href: "/lots/l-24/weigh" },
  { icon: "skull-crossbones", label: "Mortalité", href: "/lots" },
  { icon: "syringe", label: "Traitement", href: "/lots" },
  { icon: "venus-mars", label: "Saillie", href: "/repro" },
  { icon: "warehouse", label: "Mouvement de stock", href: "/" },
  { icon: "sack-dollar", label: "Vente", href: "/sales" },
];

export function QuickEntrySheet({ open, onOpenChange }: { open: boolean; onOpenChange: (open: boolean) => void }) {
  return (
    <Drawer.Root open={open} onOpenChange={onOpenChange}>
      <Drawer.Portal>
        <Drawer.Overlay className="fixed inset-0 z-40 bg-scrim" />
        <Drawer.Content className="fixed inset-x-0 bottom-0 z-50 flex flex-col gap-4 rounded-t-2xl bg-card px-5 pt-3 pb-[max(2.5rem,env(safe-area-inset-bottom))] text-card-foreground shadow-dialog outline-none">
          <Drawer.Handle className="!h-[5px] !w-10 !bg-border" />
          <div className="flex items-center justify-between">
            <Drawer.Title className="font-heading text-xl font-bold">Saisie rapide</Drawer.Title>
            <Drawer.Close
              aria-label="Fermer"
              className="grid size-10 place-items-center rounded-full bg-muted text-foreground"
            >
              <Icon name="xmark" />
            </Drawer.Close>
          </div>
          <Drawer.Description className="sr-only">Choisissez le type de saisie</Drawer.Description>
          <div className="grid grid-cols-3 gap-2.5">
            {QUICK_ACTIONS.map((action) => (
              <Link
                key={action.label}
                href={action.href}
                onClick={() => onOpenChange(false)}
                className="flex min-h-[92px] flex-col items-center gap-2.5 rounded-lg bg-muted px-2.5 py-4 text-center no-underline"
              >
                <span className="grid size-10 place-items-center rounded-xl bg-primary text-primary-foreground">
                  <Icon name={action.icon} />
                </span>
                <span className="text-meta leading-tight font-semibold text-foreground">{action.label}</span>
              </Link>
            ))}
          </div>
          <p className="flex items-center gap-2 text-xs text-muted-foreground">
            <Icon name="cloud-arrow-up" />
            Hors ligne, vos saisies sont envoyées au retour du réseau.
          </p>
        </Drawer.Content>
      </Drawer.Portal>
    </Drawer.Root>
  );
}
