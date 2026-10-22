import { cn } from "@/lib/utils";

// Font Awesome 6 Free (DS iconography). Decorative unless `label` is given.
export function Icon({
  name,
  variant = "solid",
  label,
  className,
}: {
  name: string;
  variant?: "solid" | "regular";
  label?: string;
  className?: string;
}) {
  return (
    <i
      className={cn(`fa-${variant} fa-${name}`, className)}
      aria-hidden={label ? undefined : true}
      aria-label={label}
      role={label ? "img" : undefined}
    />
  );
}
