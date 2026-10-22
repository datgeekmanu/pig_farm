import { cn } from "@/lib/utils"

type IconProps = {
  /** Font Awesome 6 Free name without prefix, e.g. "weight-scale". */
  name: string
  /** Solid for UI and navigation; regular for secondary/inactive states. */
  variant?: "solid" | "regular"
  /** Accessible label. Without it the icon is decorative and hidden from screen readers. */
  label?: string
  spin?: boolean
  className?: string
}

export function Icon({ name, variant = "solid", label, spin, className }: IconProps) {
  return (
    <i
      className={cn(`fa-${variant} fa-${name}`, spin && "fa-spin", className)}
      aria-hidden={label ? undefined : true}
      aria-label={label}
      role={label ? "img" : undefined}
    />
  )
}
