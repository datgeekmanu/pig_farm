import { Icon } from "@/components/icon"
import { inputClasses } from "@/components/ui/input"
import { parseNumber } from "@/lib/format"
import { cn } from "@/lib/utils"
import { ChipButton } from "./chip-button"

type StepperFieldProps = {
  label: string
  value: string
  onChange: (value: string) => void
  min: number
  max?: number
  /** Optional "take everything" shortcut, e.g. "Tout le lot (186)". */
  fillLabel?: string
}

export function StepperField({ label, value, onChange, min, max, fillLabel }: StepperFieldProps) {
  const count = parseNumber(value)
  const clamp = (n: number) => String(Math.max(min, max != null ? Math.min(max, n) : n))

  return (
    <div className="flex flex-wrap items-center gap-3">
      <StepButton label="Moins" icon="minus" onClick={() => onChange(clamp(count - 1))} />
      <input
        value={value}
        onChange={(event) => onChange(event.target.value)}
        inputMode="numeric"
        aria-label={label}
        className={cn(inputClasses, "h-14 w-27.5 text-center font-heading text-2xl font-bold tabular-nums")}
      />
      <StepButton label="Plus" icon="plus" onClick={() => onChange(clamp(count + 1))} />
      {fillLabel && max != null && (
        <ChipButton selected={count === max} onClick={() => onChange(String(max))}>
          {fillLabel}
        </ChipButton>
      )}
    </div>
  )
}

function StepButton({ label, icon, onClick }: { label: string; icon: string; onClick: () => void }) {
  return (
    <button
      type="button"
      aria-label={label}
      onClick={onClick}
      className="flex size-14 cursor-pointer items-center justify-center rounded-md border bg-card text-xl transition-colors hover:bg-muted"
    >
      <Icon name={icon} />
    </button>
  )
}
