"use client"

import { useState } from "react"
import { fr } from "react-day-picker/locale"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Calendar } from "@/components/ui/calendar"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { formatDate } from "@/lib/format"
import { fromIsoDate, toIsoDate, type IsoDate } from "@/lib/dates"
import { cn } from "@/lib/utils"

type DatePickerProps = {
  id?: string
  value: IsoDate | undefined
  onChange: (value: IsoDate) => void
  placeholder?: string
  size?: "default" | "lg"
  "aria-invalid"?: boolean
}

export function DatePicker({ id, value, onChange, placeholder = "Choisir une date", size = "default", ...aria }: DatePickerProps) {
  const [open, setOpen] = useState(false)
  const selected = value ? fromIsoDate(value) : undefined

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button
          id={id}
          variant="outline"
          size={size}
          aria-invalid={aria["aria-invalid"]}
          className={cn("w-full justify-start font-normal", !selected && "text-muted-foreground")}
        >
          <Icon name="calendar" variant="regular" className="text-muted-foreground" />
          {selected ? formatDate(selected, "full") : placeholder}
        </Button>
      </PopoverTrigger>
      <PopoverContent className="w-auto p-0" align="start">
        <Calendar
          mode="single"
          locale={fr}
          selected={selected}
          defaultMonth={selected}
          onSelect={(date) => {
            if (!date) return
            onChange(toIsoDate(date))
            setOpen(false)
          }}
          className="[--cell-size:--spacing(9)]"
        />
      </PopoverContent>
    </Popover>
  )
}
