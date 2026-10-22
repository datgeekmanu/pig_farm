import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

type ChipButtonProps = { selected: boolean; onClick: () => void; children: React.ReactNode }

/** 48px pill toggle; filled forest when selected. */
export function ChipButton({ selected, onClick, children }: ChipButtonProps) {
  return (
    <button
      type="button"
      aria-pressed={selected}
      onClick={onClick}
      className={cn(
        "flex min-h-12 cursor-pointer items-center gap-2 rounded-full border px-4.5 text-body font-semibold transition-colors",
        selected ? "border-primary bg-primary text-primary-foreground" : "bg-card text-foreground hover:bg-muted",
      )}
    >
      {selected && <Icon name="check" />}
      {children}
    </button>
  )
}
