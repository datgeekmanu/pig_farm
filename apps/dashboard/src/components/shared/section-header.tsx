import { cn } from "@/lib/utils"

type SectionHeaderProps = {
  title: React.ReactNode
  /** Right-hand slot: a "Voir tout" link, a count, a hint… */
  aside?: React.ReactNode
  size?: "lg" | "md"
  className?: string
}

export function SectionHeader({ title, aside, size = "lg", className }: SectionHeaderProps) {
  return (
    <div className={cn("flex flex-wrap items-baseline justify-between gap-3", className)}>
      <h2 className={size === "lg" ? "text-section" : "text-lg leading-6"}>{title}</h2>
      {aside && <div className="text-sm text-muted-foreground">{aside}</div>}
    </div>
  )
}
