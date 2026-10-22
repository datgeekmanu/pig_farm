"use client"

import { useId } from "react"
import { Field, FieldLabel } from "@/components/ui/field"
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from "@/components/ui/select"

type SelectOption<T extends string> = { value: T; label: string }

type SelectFieldProps<T extends string> = {
  label: string
  options: SelectOption<T>[]
  value: T
  onValueChange: (value: T) => void
  required?: boolean
}

/** Labelled shadcn Select with a fixed option list. */
export function SelectField<T extends string>({ label, options, value, onValueChange, required }: SelectFieldProps<T>) {
  const id = useId()

  return (
    <Field>
      <FieldLabel htmlFor={id}>
        {label}
        {required && (
          <span aria-hidden className="text-destructive">
            *
          </span>
        )}
      </FieldLabel>
      <Select value={value} onValueChange={(next) => onValueChange(next as T)}>
        <SelectTrigger id={id} className="h-10 w-full bg-card text-base">
          <SelectValue />
        </SelectTrigger>
        <SelectContent>
          {options.map((option) => (
            <SelectItem key={option.value} value={option.value}>
              {option.label}
            </SelectItem>
          ))}
        </SelectContent>
      </Select>
    </Field>
  )
}
