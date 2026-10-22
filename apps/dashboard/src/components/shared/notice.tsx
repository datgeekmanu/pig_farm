import { Icon } from "@/components/icon"
import { cn } from "@/lib/utils"

type NoticeTone = "info" | "success" | "warning" | "danger"

const TONES: Record<NoticeTone, { icon: string; className: string }> = {
  info: { icon: "circle-info", className: "text-info" },
  success: { icon: "circle-check", className: "text-success" },
  warning: { icon: "triangle-exclamation", className: "text-warning" },
  danger: { icon: "circle-xmark", className: "text-danger" },
}

type NoticeProps = { tone?: NoticeTone; title: string; children?: React.ReactNode }

/** Inline feedback: coloured icon + title + description, no box. */
export function Notice({ tone = "info", title, children }: NoticeProps) {
  const { icon, className } = TONES[tone]

  return (
    <div role={tone === "danger" ? "alert" : "status"} className={cn("flex items-start gap-2.5 text-sm", className)}>
      <Icon name={icon} className="w-4.5 flex-none text-center leading-5" />
      <div className="min-w-0 flex-1">
        <p className="font-bold">{title}</p>
        {children && <p>{children}</p>}
      </div>
    </div>
  )
}
