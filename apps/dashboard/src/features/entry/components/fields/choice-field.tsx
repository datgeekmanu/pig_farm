import { Icon } from "@/components/icon"
import { InitialsAvatar } from "@/components/shared/initials-avatar"
import { cn } from "@/lib/utils"
import type { ChoiceOption } from "../../types"

type ChoiceFieldProps = { options: ChoiceOption[]; value: string; onChange: (value: string) => void }

/** Large tappable cards, one per option (lots, sows, stock items). */
export function ChoiceField({ options, value, onChange }: ChoiceFieldProps) {
  return (
    <div role="radiogroup" className="flex flex-col gap-2">
      {options.map((option) => {
        const selected = option.value === value
        return (
          <button
            key={option.value}
            type="button"
            role="radio"
            aria-checked={selected}
            onClick={() => onChange(option.value)}
            className={cn(
              "flex min-h-16 cursor-pointer items-center gap-3.5 rounded-lg bg-card px-3.5 py-2.5 text-left text-card-foreground",
              selected ? "border-2 border-ring" : "border",
            )}
          >
            <InitialsAvatar size="sm" color={option.color}>
              {option.short}
            </InitialsAvatar>
            <span className="flex flex-1 flex-col">
              <span className="text-base font-semibold">{option.label}</span>
              {option.sub && <span className="text-caption text-muted-foreground">{option.sub}</span>}
            </span>
            <Icon
              name="circle-check"
              variant={selected ? "solid" : "regular"}
              className={cn("text-xl", selected ? "text-primary" : "text-border")}
            />
          </button>
        )
      })}
    </div>
  )
}
