import * as React from "react"
import { cn } from "@/lib/utils"

// Focus = ink border (1.5px foreground), never a coloured halo.
const inputClasses =
  "h-10 w-full min-w-0 rounded-md border border-input bg-card px-3 text-base text-foreground transition-colors outline-none placeholder:text-muted-foreground focus-visible:border-foreground focus-visible:outline-none focus-visible:ring-[0.5px] focus-visible:ring-foreground disabled:cursor-not-allowed disabled:opacity-60 aria-invalid:border-destructive aria-invalid:ring-destructive read-only:bg-muted"

function Input({ className, type, ...props }: React.ComponentProps<"input">) {
  return <input type={type} data-slot="input" className={cn(inputClasses, className)} {...props} />
}

export { Input, inputClasses }
