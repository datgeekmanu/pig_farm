"use client"

import * as React from "react"
import { Switch as SwitchPrimitive } from "radix-ui"
import { cn } from "@/lib/utils"

type SwitchProps = React.ComponentProps<typeof SwitchPrimitive.Root> & {
  /** Dark track with a lime "live" knob, for use on the forest sidebar. */
  onDark?: boolean
}

function Switch({ className, onDark = false, ...props }: SwitchProps) {
  return (
    <SwitchPrimitive.Root
      data-slot="switch"
      data-on-dark={onDark || undefined}
      className={cn(
        "peer group/switch relative inline-flex h-6.5 w-11.5 shrink-0 cursor-pointer items-center rounded-full transition-colors duration-200 ease-out outline-none disabled:cursor-not-allowed disabled:opacity-50",
        "data-[state=checked]:bg-primary data-[state=unchecked]:bg-input",
        "data-on-dark:border data-on-dark:border-sidebar-border data-on-dark:data-[state=checked]:bg-sidebar-track data-on-dark:data-[state=unchecked]:bg-sidebar-track",
        className,
      )}
      {...props}
    >
      <SwitchPrimitive.Thumb
        data-slot="switch-thumb"
        className={cn(
          "pointer-events-none block size-5 rounded-full bg-white shadow-sm transition-transform duration-200 ease-out data-[state=checked]:translate-x-5.75 data-[state=unchecked]:translate-x-0.75",
          "group-data-on-dark/switch:shadow-none group-data-[state=checked]/switch:bg-primary-foreground group-data-on-dark/switch:data-[state=checked]:bg-live",
        )}
      />
    </SwitchPrimitive.Root>
  )
}

export { Switch }
