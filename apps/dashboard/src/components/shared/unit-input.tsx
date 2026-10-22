import { InputGroup, InputGroupAddon, InputGroupInput } from "@/components/ui/input-group"
import { cn } from "@/lib/utils"

type UnitInputProps = Omit<React.ComponentProps<"input">, "type" | "size"> & {
  /** Unit shown after the value, e.g. "kg", "FCFA/kg". */
  unit?: string
  size?: "default" | "lg"
}

/** Numeric field: right-aligned tabular figures with a trailing unit. */
export function UnitInput({ unit, size = "default", className, ...props }: UnitInputProps) {
  return (
    <InputGroup className={cn(size === "lg" && "h-12")}>
      <InputGroupInput inputMode="decimal" autoComplete="off" className={cn("text-right tabular-nums", className)} {...props} />
      {unit && <InputGroupAddon align="inline-end">{unit}</InputGroupAddon>}
    </InputGroup>
  )
}
