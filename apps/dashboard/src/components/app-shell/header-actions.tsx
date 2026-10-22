"use client"

import Link from "next/link"
import { useTheme } from "next-themes"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { NotificationsPopover } from "@/features/alerts/notifications-popover"
import { useOnline } from "@/hooks/use-online"
import { cn } from "@/lib/utils"

export function HeaderActions() {
  return (
    <div className="ml-auto flex flex-none items-center gap-2">
      <NetworkStatus />
      <ThemeToggle />
      <NotificationsPopover />
      <Button asChild>
        <Link href="/entry">
          <Icon name="plus" />
          Saisir
        </Link>
      </Button>
    </div>
  )
}

// TODO(#11.1): show queued offline entries ("Hors ligne · 2 en attente").
function NetworkStatus() {
  const online = useOnline()

  return (
    <span
      role="status"
      className={cn(
        "flex h-8 items-center gap-2 rounded-full border bg-card px-3 text-caption font-semibold whitespace-nowrap max-sm:hidden",
        online ? "text-success" : "text-warning",
      )}
    >
      <Icon name={online ? "circle-check" : "cloud-arrow-up"} />
      {online ? "En ligne · à jour" : "Hors ligne"}
    </span>
  )
}

function ThemeToggle() {
  const { resolvedTheme, setTheme } = useTheme()

  return (
    <Button variant="ghost" size="icon" aria-label="Changer de thème" onClick={() => setTheme(resolvedTheme === "dark" ? "light" : "dark")}>
      {/* Both icons rendered and swapped by CSS, so SSR and client markup match. */}
      <Icon name="moon" className="dark:hidden" />
      <Icon name="sun" className="hidden dark:inline-block" />
    </Button>
  )
}
