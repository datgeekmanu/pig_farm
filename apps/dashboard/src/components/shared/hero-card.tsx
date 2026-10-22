import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"
import { CornerLink } from "./corner-link"
import { Pill } from "./pill"

type HeroCardProps = {
  label: string
  value: React.ReactNode
  icon?: string
  /** Crimson change pill next to the value, e.g. "+2,1 % sur 30 j". */
  change?: string
  caption?: React.ReactNode
  link?: { href: string; label: string }
  size?: "lg" | "md"
}

/** The one earth-brown hero figure per screen. */
export function HeroCard({ label, value, icon, change, caption, link, size = "lg" }: HeroCardProps) {
  return (
    <section className="relative flex min-h-28 flex-col gap-1.5 rounded-xl bg-hero p-5 text-hero-foreground">
      <div className="flex items-center gap-2.5 text-body font-semibold">
        {icon && <Icon name={icon} />}
        {label}
      </div>
      {link && <CornerLink href={link.href} label={link.label} variant="onHero" />}
      <div className="mt-auto flex flex-wrap items-baseline gap-2.5">
        <span className={cn("font-heading font-bold tabular-nums", size === "lg" ? "text-figure" : "text-3xl")}>{value}</span>
        {change && <Pill>{change}</Pill>}
      </div>
      {caption && <p className="text-caption text-hero-muted">{caption}</p>}
    </section>
  )
}
