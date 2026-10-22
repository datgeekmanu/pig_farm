"use client";

import Link from "next/link";
import { usePathname } from "next/navigation";
import { useState } from "react";
import { Icon } from "@/components/ui/icon";
import { cn } from "@/lib/utils";
import { QuickEntrySheet } from "./quick-entry-sheet";

const TABS = [
  { href: "/", icon: "house", label: "Accueil" },
  { href: "/lots", icon: "layer-group", label: "Lots" },
  null, // quick-entry button
  { href: "/repro", icon: "venus-mars", label: "Repro" },
  { href: "/sales", icon: "store", label: "Ventes" },
];

function isActive(pathname: string, href: string) {
  return href === "/" ? pathname === "/" : pathname.startsWith(href);
}

export function TabBar() {
  const pathname = usePathname();
  const [sheetOpen, setSheetOpen] = useState(false);

  return (
    <>
      <nav
        aria-label="Navigation principale"
        className="fixed inset-x-0 bottom-0 z-30 grid grid-cols-5 items-start border-t border-border bg-card pt-2.5 pb-[max(1.5rem,env(safe-area-inset-bottom))]"
      >
        {TABS.map((tab) =>
          tab ? (
            <Link
              key={tab.href}
              href={tab.href}
              aria-current={isActive(pathname, tab.href) ? "page" : undefined}
              className={cn(
                "flex flex-col items-center gap-1 text-2xs font-semibold text-muted-foreground no-underline",
                isActive(pathname, tab.href) && "font-bold text-primary",
              )}
            >
              <Icon name={tab.icon} className="text-lg" />
              {tab.label}
            </Link>
          ) : (
            <div key="quick" className="flex justify-center">
              <button
                type="button"
                onClick={() => setSheetOpen(true)}
                aria-label="Saisie rapide"
                className="-mt-6.5 grid size-14 place-items-center rounded-full border-4 border-background bg-primary text-xl text-primary-foreground"
              >
                <Icon name="plus" />
              </button>
            </div>
          ),
        )}
      </nav>
      <QuickEntrySheet open={sheetOpen} onOpenChange={setSheetOpen} />
    </>
  );
}
