"use client"

import { ToggleGroup } from "radix-ui"
import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

type SegmentOption<T extends string> = { value: T; label: string; icon?: string; disabled?: boolean }

type SegmentedControlProps<T extends string> = {
  label: string
  options: SegmentOption<T>[]
  value: T
  onValueChange: (value: T) => void
  size?: "sm" | "md"
}

export function SegmentedControl<T extends string>({ label, options, value, onValueChange, size = "md" }: SegmentedControlProps<T>) {
  return (
    <ToggleGroup.Root
      type="single"
      aria-label={label}
      value={value}
      // Radix emits "" when the active item is clicked again; keep the selection.
      onValueChange={(next) => next && onValueChange(next as T)}
      className="flex rounded-md bg-muted p-0.5"
    >
      {options.map((option) => (
        <ToggleGroup.Item
          key={option.value}
          value={option.value}
          disabled={option.disabled}
          className={cn(
            "flex cursor-pointer items-center gap-2 rounded-[6px] font-semibold text-muted-foreground transition-colors disabled:cursor-not-allowed disabled:opacity-60 data-[state=on]:bg-card data-[state=on]:text-foreground data-[state=on]:shadow-xs",
            size === "md" ? "h-9 px-3.5 text-sm" : "h-8 px-3 text-caption",
          )}
        >
          {option.icon && <Icon name={option.icon} />}
          {option.label}
        </ToggleGroup.Item>
      ))}
    </ToggleGroup.Root>
  )
}
