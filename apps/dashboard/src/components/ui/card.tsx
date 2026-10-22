import * as React from "react"
import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

// Cards: white surface, 1px border, radius 24, no shadow.
function Card({ className, size = "default", ...props }: React.ComponentProps<"section"> & { size?: "default" | "sm" | "flush" }) {
  return (
    <section
      data-slot="card"
      data-size={size}
      className={cn(
        "group/card flex min-w-0 flex-col gap-4 rounded-xl border bg-card p-6 text-card-foreground data-[size=flush]:gap-0 data-[size=flush]:overflow-hidden data-[size=flush]:p-0 data-[size=sm]:p-5",
        className,
      )}
      {...props}
    />
  )
}

type CardHeaderProps = Omit<React.ComponentProps<"header">, "title"> & {
  title: React.ReactNode
  description?: React.ReactNode
  /** Optional 36px icon tile before the title. */
  icon?: string
  action?: React.ReactNode
}

function CardHeader({ title, description, icon, action, className, ...props }: CardHeaderProps) {
  return (
    <header data-slot="card-header" className={cn("flex items-start gap-3", className)} {...props}>
      {icon && <IconTile name={icon} />}
      <div className="min-w-0 flex-1">
        <h3 className="text-lg leading-7">{title}</h3>
        {description && <p className="text-sm text-muted-foreground">{description}</p>}
      </div>
      {action && <div className="flex flex-none gap-2">{action}</div>}
    </header>
  )
}

/** 36px muted square holding an icon; used by card headers and lists. */
function IconTile({ name, className }: { name: string; className?: string }) {
  return (
    <span className={cn("flex size-9 flex-none items-center justify-center rounded-md bg-muted text-primary", className)}>
      <Icon name={name} />
    </span>
  )
}

export { Card, CardHeader, IconTile }
