import Link from "next/link"
import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

type CornerLinkProps = {
  href: string
  label: string
  /** "onHero" renders a translucent button for the earth hero card. */
  variant?: "default" | "onHero"
}

/** Diagonal-arrow shortcut pinned to the top-right corner of a stat card. */
export function CornerLink({ href, label, variant = "default" }: CornerLinkProps) {
  return (
    <Link
      href={href}
      aria-label={label}
      className={cn(
        "absolute top-3.5 right-3.5 flex size-9 items-center justify-center rounded-md transition-colors",
        variant === "onHero" ? "bg-white/15 text-white hover:bg-white/25" : "border bg-card text-foreground hover:bg-muted",
      )}
    >
      <Icon name="arrow-right" className="-rotate-45" />
    </Link>
  )
}
