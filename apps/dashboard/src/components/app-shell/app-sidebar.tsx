"use client"

import Image from "next/image"
import Link from "next/link"
import { Icon } from "@/components/icon"
import { Switch } from "@/components/ui/switch"
import { REMINDER_CATEGORIES, useReminderSettings } from "@/features/alerts/reminder-settings"
import { useTodayTasks } from "@/features/alerts/today-tasks"
import { useIsOwner, useLogout } from "@/features/auth/queries"
import { entryHref } from "@/features/entry/links"
import type { FlowKey } from "@/features/entry/types"
import { FarmSwitcher } from "@/features/farms/farm-switcher"
import { UserAvatar } from "./user-avatar"

type Shortcut = { flow: FlowKey; label: string; icon: string; updated: string }

// TODO(#11.1): "MAJ" dates come from the last sync per entry type.
const SHORTCUTS: Shortcut[] = [
  { flow: "weighing", label: "Pesée", icon: "weight-scale", updated: "17.10" },
  { flow: "mortality", label: "Mortalité", icon: "skull-crossbones", updated: "16.10" },
  { flow: "treatment", label: "Traitement", icon: "syringe", updated: "14.10" },
]
const OWNER_SHORTCUT: Shortcut = { flow: "sale", label: "Vente", icon: "sack-dollar", updated: "17.10" }
const WORKER_SHORTCUT: Shortcut = { flow: "service", label: "Saillie", icon: "venus-mars", updated: "15.10" }

/** Floating forest widget panel: farm, automatic reminders, quick field entry. */
export function AppSidebar() {
  const isOwner = useIsOwner()
  const shortcuts = [...SHORTCUTS, isOwner ? OWNER_SHORTCUT : WORKER_SHORTCUT]

  return (
    <aside className="flex h-full w-80 flex-none flex-col overflow-hidden rounded-2xl bg-sidebar text-sidebar-foreground">
      <div className="flex items-center gap-3 border-b border-sidebar-border px-6 pt-6 pb-5">
        <Link href="/" className="flex flex-1">
          <Image src="/brand/pigfarm-logo-horizontal-reversed.png" alt="Pig Farm" width={97} height={34} priority />
        </Link>
        <UserAvatar />
      </div>

      <div className="flex flex-1 flex-col gap-7 overflow-auto px-5 pt-5 pb-6">
        <FarmSwitcher />
        <ReminderToggles />
        <section className="flex flex-col gap-3">
          <SidebarHeading title="Saisie rapide" aside="Terrain" />
          <div className="grid grid-cols-2 gap-3">
            {shortcuts.map((shortcut) => (
              <Link
                key={shortcut.flow}
                href={entryHref(shortcut.flow)}
                className="flex min-h-29 flex-col items-start gap-1 rounded-lg bg-sidebar-tile p-4 text-white transition-colors hover:bg-sidebar-tile-hover"
              >
                <Icon name={shortcut.icon} className="text-[19px]" />
                <span className="mt-auto text-body font-semibold">{shortcut.label}</span>
                <span className="text-xs text-sidebar-muted tabular-nums">MAJ {shortcut.updated}</span>
              </Link>
            ))}
          </div>
        </section>
      </div>

      <SidebarFooter />
    </aside>
  )
}

function ReminderToggles() {
  const { enabled, setEnabled } = useReminderSettings()
  const activeCount = useTodayTasks().length

  return (
    <section className="flex flex-col gap-3">
      <SidebarHeading title="Rappels automatiques" aside={`${activeCount} actifs`} />
      {REMINDER_CATEGORIES.map((category) => (
        <label key={category.key} className="flex cursor-pointer items-center gap-3.5 rounded-lg bg-sidebar-tile px-4 py-3.5">
          <Icon name={category.icon} className="w-5 text-center text-white" />
          <span className="flex-1 text-body font-semibold">{category.label}</span>
          <Switch onDark checked={enabled[category.key]} onCheckedChange={(on) => setEnabled(category.key, on)} />
        </label>
      ))}
    </section>
  )
}

function SidebarHeading({ title, aside }: { title: string; aside: string }) {
  return (
    <div className="mx-1 flex items-baseline justify-between">
      <h2 className="text-lg leading-6 text-white">{title}</h2>
      <span className="text-caption text-sidebar-muted">{aside}</span>
    </div>
  )
}

function SidebarFooter() {
  const logout = useLogout()

  return (
    <div className="flex gap-2 border-t border-sidebar-border px-5 py-3.5">
      {/* TODO(#7.1): farm settings screen */}
      <button
        type="button"
        className="flex h-11 flex-1 cursor-pointer items-center gap-2.5 rounded-md px-3.5 text-sm font-semibold transition-colors hover:bg-sidebar-active"
      >
        <Icon name="gear" />
        Paramètres
      </button>
      <button
        type="button"
        aria-label="Se déconnecter"
        onClick={() => logout.mutate()}
        className="flex size-11 cursor-pointer items-center justify-center rounded-md text-sidebar-muted transition-colors hover:bg-sidebar-active"
      >
        <Icon name="arrow-right-from-bracket" />
      </button>
    </div>
  )
}
