import { Icon } from "@/components/icon"

export function EmptyState({ icon, children }: { icon: string; children: React.ReactNode }) {
  return (
    <div className="flex flex-col items-center gap-2.5 rounded-xl border border-dashed px-6 py-14 text-center text-muted-foreground">
      <Icon name={icon} className="text-[22px]" />
      <p className="text-body">{children}</p>
    </div>
  )
}
