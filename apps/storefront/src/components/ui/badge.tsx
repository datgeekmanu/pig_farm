import { cva, type VariantProps } from "class-variance-authority";
import { Icon } from "./icon";

// Status = coloured icon + coloured text, no fill (DS rule).
const badgeVariants = cva(
  "inline-flex items-center gap-1.5 rounded-full font-semibold whitespace-nowrap tabular-nums",
  {
    variants: {
      tone: {
        neutral: "text-muted-foreground",
        primary: "text-primary",
        success: "text-success",
        warning: "text-warning",
        danger: "text-danger",
        info: "text-info",
      },
      outline: {
        true: "h-[26px] self-start border border-border bg-card px-2.5 text-meta",
        false: "h-5 text-meta",
      },
    },
    compoundVariants: [{ tone: "neutral", outline: true, className: "text-foreground" }],
    defaultVariants: { tone: "neutral", outline: false },
  },
);

const TONE_ICONS: Record<string, string> = {
  success: "circle-check",
  warning: "triangle-exclamation",
  danger: "circle-xmark",
  info: "circle-info",
};

export function Badge({
  tone,
  outline,
  icon,
  children,
}: VariantProps<typeof badgeVariants> & { icon?: string; children: React.ReactNode }) {
  const iconName = icon ?? TONE_ICONS[tone ?? "neutral"];

  return (
    <span className={badgeVariants({ tone, outline })}>
      {iconName && <Icon name={iconName} className="text-xs" />}
      {children}
    </span>
  );
}
