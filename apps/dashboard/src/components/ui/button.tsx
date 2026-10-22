import * as React from "react"
import { cva, type VariantProps } from "class-variance-authority"
import { Slot } from "radix-ui"
import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

const buttonVariants = cva(
  "inline-flex shrink-0 cursor-pointer items-center justify-center gap-2 rounded-md border border-transparent font-semibold whitespace-nowrap transition-colors duration-150 ease-out outline-none select-none active:translate-y-px disabled:pointer-events-none disabled:opacity-50 aria-invalid:border-destructive [&_svg]:pointer-events-none [&_svg]:shrink-0 [&_svg:not([class*='size-'])]:size-4",
  {
    variants: {
      variant: {
        default: "bg-primary text-primary-foreground hover:bg-primary-hover",
        secondary: "bg-secondary text-secondary-foreground hover:bg-secondary-hover",
        outline: "border-border bg-card text-foreground hover:bg-muted aria-expanded:bg-muted",
        ghost: "text-foreground hover:bg-hover-overlay aria-expanded:bg-hover-overlay",
        destructive: "bg-destructive text-destructive-foreground hover:bg-destructive-hover",
        link: "text-primary underline-offset-4 hover:underline",
      },
      size: {
        default: "h-10 px-4 text-sm",
        xs: "h-6 gap-1 px-2 text-xs",
        sm: "h-8 gap-1.5 px-3 text-sm",
        lg: "h-12 px-5 text-base",
        icon: "size-10",
        "icon-xs": "size-6 text-xs",
        "icon-sm": "size-8",
        "icon-lg": "size-11",
      },
    },
    defaultVariants: {
      variant: "default",
      size: "default",
    },
  },
)

type ButtonProps = React.ComponentProps<"button"> &
  VariantProps<typeof buttonVariants> & {
    asChild?: boolean
    /** Shows a spinner and blocks clicks while an action runs. */
    loading?: boolean
  }

function Button({
  className,
  variant = "default",
  size = "default",
  asChild = false,
  loading = false,
  disabled,
  children,
  ...props
}: ButtonProps) {
  const Comp = asChild ? Slot.Root : "button"

  return (
    <Comp
      data-slot="button"
      data-variant={variant}
      data-size={size}
      className={cn(buttonVariants({ variant, size, className }))}
      disabled={disabled || loading}
      aria-busy={loading || undefined}
      {...props}
    >
      {/* Slot (asChild) accepts exactly one child, so no spinner slot there. */}
      {asChild ? (
        children
      ) : (
        <>
          {loading && <Icon name="spinner" spin />}
          {children}
        </>
      )}
    </Comp>
  )
}

export { Button, buttonVariants }
