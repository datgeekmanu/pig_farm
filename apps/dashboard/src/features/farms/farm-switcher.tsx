"use client"

import { toast } from "sonner"
import { Icon } from "@/components/icon"
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuTrigger } from "@/components/ui/dropdown-menu"
import { currencyLabel } from "@/lib/format"
import { useActiveFarm } from "./active-farm"

export function FarmSwitcher() {
  const { farm, farms, selectFarm } = useActiveFarm()

  const pick = (id: string, name: string) => {
    if (id === farm.id) return
    selectFarm(id)
    toast.success(`Ferme active : ${name}`, { description: "Toutes les données affichées concernent désormais cette ferme." })
  }

  return (
    <DropdownMenu>
      <DropdownMenuTrigger className="flex w-full cursor-pointer items-center gap-3 rounded-lg bg-sidebar-tile px-3.5 py-3 text-left transition-colors hover:bg-sidebar-tile-hover">
        <Icon name="location-dot" className="text-live" />
        <FarmLabel name={farm.name} place={`${farm.place} · ${currencyLabel(farm.currency)}`} muted="text-sidebar-muted" />
        <Icon name="chevron-down" className="text-[11px] text-sidebar-muted" />
      </DropdownMenuTrigger>
      <DropdownMenuContent align="start" className="w-(--radix-dropdown-menu-trigger-width) rounded-lg p-1.5">
        {farms.map((option) => (
          <DropdownMenuItem key={option.id} onSelect={() => pick(option.id, option.name)} className="gap-2.5 px-2.5 py-2">
            <FarmLabel name={option.name} place={`${option.place} · ${currencyLabel(option.currency)}`} muted="text-muted-foreground" />
            {option.id === farm.id && <Icon name="check" className="text-primary" />}
          </DropdownMenuItem>
        ))}
      </DropdownMenuContent>
    </DropdownMenu>
  )
}

function FarmLabel({ name, place, muted }: { name: string; place: string; muted: string }) {
  return (
    <span className="flex min-w-0 flex-1 flex-col">
      <span className="text-body font-semibold">{name}</span>
      <span className={`text-xs ${muted}`}>{place}</span>
    </span>
  )
}
