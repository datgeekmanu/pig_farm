import { cva, type VariantProps } from "class-variance-authority"

// Small solid callouts: crimson for change/highlight, forest for labels on photos.
const pillVariants = cva("inline-flex items-center gap-2 rounded-md px-2.5 py-1 text-caption font-semibold tabular-nums", {
  variants: {
    tone: {
      highlight: "bg-highlight text-highlight-foreground",
      brand: "bg-brand text-white",
    },
  },
  defaultVariants: { tone: "highlight" },
})

type PillProps = VariantProps<typeof pillVariants> & { children: React.ReactNode }

export function Pill({ tone, children }: PillProps) {
  return <span className={pillVariants({ tone })}>{children}</span>
}
