import { cn } from "@/lib/utils"

type ProgressBarProps = {
  /** 0–100 */
  value: number
  /** Positions (0–100) of tick markers, e.g. thresholds or milestones. */
  markers?: number[]
  tone?: "primary" | "danger"
  size?: "sm" | "md"
  label?: string
}

export function ProgressBar({ value, markers = [], tone = "primary", size = "sm", label }: ProgressBarProps) {
  return (
    <div
      role="progressbar"
      aria-label={label}
      aria-valuenow={Math.round(value)}
      aria-valuemin={0}
      aria-valuemax={100}
      className={cn("relative rounded-full bg-muted", size === "sm" ? "h-1.5" : "h-2")}
    >
      <div className={cn("h-full rounded-full", tone === "danger" ? "bg-danger" : "bg-primary")} style={{ width: `${clamp(value)}%` }} />
      {markers.map((marker) => (
        <span key={marker} className="absolute -top-0.75 h-3.5 w-0.5 bg-muted-foreground" style={{ left: `${clamp(marker)}%` }} />
      ))}
    </div>
  )
}

const clamp = (n: number) => Math.min(100, Math.max(0, n))
