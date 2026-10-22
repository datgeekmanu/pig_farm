import { Icon } from "@/components/ui/icon";
import { cn } from "@/lib/utils";

const ICON_TONES = {
  ring: "text-ring",
  success: "text-success",
  warning: "text-warning",
  danger: "text-danger",
};

export type IconTone = keyof typeof ICON_TONES;

// 36px muted square holding an icon (DS card icon tile).
export function IconTile({ icon, tone = "ring" }: { icon: string; tone?: IconTone }) {
  return (
    <span className={cn("grid size-9 flex-none place-items-center rounded-[10px] bg-muted", ICON_TONES[tone])}>
      <Icon name={icon} />
    </span>
  );
}

export function Stat({ icon, value, label, tone }: { icon: string; value: string; label: string; tone?: IconTone }) {
  return (
    <div className="flex items-center gap-2.5">
      <IconTile icon={icon} tone={tone} />
      <div className="flex flex-col">
        <span className="font-heading text-lg font-bold tabular-nums">{value}</span>
        <span className="text-xs text-muted-foreground">{label}</span>
      </div>
    </div>
  );
}
