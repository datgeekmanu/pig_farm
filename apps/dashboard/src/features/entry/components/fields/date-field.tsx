import { subDays } from "date-fns"
import { DatePicker } from "@/components/shared/date-picker"
import { today, toIsoDate, type IsoDate } from "@/lib/dates"
import { ChipButton } from "./chip-button"

type DateFieldProps = { value: IsoDate; onChange: (value: IsoDate) => void }

/** "Aujourd'hui" / "Hier" shortcuts cover most field entries; the picker covers the rest. */
export function DateField({ value, onChange }: DateFieldProps) {
  const shortcuts = [
    { label: "Aujourd’hui", date: toIsoDate(today()) },
    { label: "Hier", date: toIsoDate(subDays(today(), 1)) },
  ]

  return (
    <div className="flex flex-wrap items-center gap-2">
      {shortcuts.map((shortcut) => (
        <ChipButton key={shortcut.label} selected={value === shortcut.date} onClick={() => onChange(shortcut.date)}>
          {shortcut.label}
        </ChipButton>
      ))}
      <div className="min-w-60 flex-1">
        <DatePicker value={value} onChange={onChange} size="lg" placeholder="Autre date" />
      </div>
    </div>
  )
}
