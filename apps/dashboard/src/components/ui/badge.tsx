// Vendored shadcn/ui primitive: keep its full export surface.
// fallow-ignore-file unused-export

import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

/** Text colour per status tone; also used for status icons outside badges. */
export const TONE_TEXT = {
  neutral: "text-muted-foreground",
  primary: "text-primary",
  success: "text-success",
  warning: "text-warning",
  danger: "text-danger",
  info: "text-info",
} as const

// Status is shown as coloured icon + text, never a filled pill (see design system).
const badgeVariants = cva("inline-flex w-fit shrink-0 items-center gap-1.5 font-semibold whitespace-nowrap tabular-nums", {
  variants: {
    tone: TONE_TEXT,
    size: {
      default: "h-5 text-caption [&_i]:text-xs",
      sm: "h-4.5 text-xs [&_i]:text-[11px]",
    },
  },
  defaultVariants: {
    tone: "neutral",
    size: "default",
  },
})

export type Tone = NonNullable<VariantProps<typeof badgeVariants>["tone"]>

const TONE_ICONS: Partial<Record<Tone, string>> = {
  success: "circle-check",
  warning: "triangle-exclamation",
  danger: "circle-xmark",
  info: "circle-info",
}

type BadgeProps = React.ComponentProps<"span"> &
  VariantProps<typeof badgeVariants> & {
    /** Overrides the tone's default icon. */
    icon?: string
  }

function Badge({ tone = "neutral", size, icon, className, children, ...props }: BadgeProps) {
  const iconName = icon ?? TONE_ICONS[tone ?? "neutral"]

  return (
    <span data-slot="badge" className={cn(badgeVariants({ tone, size }), className)} {...props}>
      {iconName && <Icon name={iconName} />}
      {children}
    </span>
  )
}

export { Badge, badgeVariants }
