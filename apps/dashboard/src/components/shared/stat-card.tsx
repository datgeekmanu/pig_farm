import { Card, IconTile } from "@/components/ui/card"
import { cn } from "@/lib/utils"
import { CornerLink } from "./corner-link"

type StatCardProps = {
  label: string
  value: React.ReactNode
  unit?: string
  icon?: string
  /** Status line under the figure, usually a <Badge>. */
  footer?: React.ReactNode
  link?: { href: string; label: string }
  /** "md" for secondary KPI rows (30px figure instead of 36px). */
  size?: "lg" | "md"
}

export function StatCard({ label, value, unit, icon, footer, link, size = "lg" }: StatCardProps) {
  return (
    <Card size="sm" className={cn("relative gap-1.5", size === "lg" ? "min-h-38" : "min-h-28")}>
      <div className="flex items-center gap-2.5 pr-11">
        {icon && <IconTile name={icon} />}
        <span className="text-body font-semibold">{label}</span>
      </div>
      {link && <CornerLink href={link.href} label={link.label} />}
      <div className="mt-auto flex items-baseline gap-1.5">
        <span className={cn("font-heading font-bold tabular-nums", size === "lg" ? "text-figure" : "text-3xl")}>{value}</span>
        {unit && <span className="font-semibold text-muted-foreground">{unit}</span>}
      </div>
      {footer}
    </Card>
  )
}
