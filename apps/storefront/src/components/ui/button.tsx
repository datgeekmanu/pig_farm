import { cva, type VariantProps } from "class-variance-authority";
import { Slot } from "radix-ui";
import { Icon } from "./icon";

const buttonVariants = cva(
  "inline-flex shrink-0 items-center justify-center rounded-md border font-semibold whitespace-nowrap transition-colors duration-150 ease-out active:translate-y-px disabled:pointer-events-none disabled:opacity-50",
  {
    variants: {
      variant: {
        primary: "border-transparent bg-primary text-primary-foreground hover:bg-primary-hover",
        secondary: "border-transparent bg-secondary text-secondary-foreground hover:bg-secondary-hover",
        outline: "border-border bg-card text-foreground hover:bg-muted",
        ghost: "border-transparent bg-transparent text-foreground hover:bg-hover-overlay",
        destructive: "border-transparent bg-destructive text-destructive-foreground hover:bg-destructive-hover",
      },
      size: {
        sm: "h-8 gap-1.5 px-3 text-sm",
        md: "h-10 gap-2 px-4 text-sm",
        lg: "h-12 gap-2 px-5 text-base",
      },
      iconOnly: { true: "px-0" },
      fullWidth: { true: "flex w-full" },
    },
    compoundVariants: [
      { iconOnly: true, size: "sm", className: "w-8" },
      { iconOnly: true, size: "md", className: "w-10" },
      { iconOnly: true, size: "lg", className: "w-12" },
    ],
    defaultVariants: { variant: "primary", size: "md" },
  },
);

type ButtonProps = Omit<React.ComponentProps<"button">, "className"> &
  VariantProps<typeof buttonVariants> & {
    icon?: string;
    loading?: boolean;
    // Render the child element (e.g. a Link) with button styling.
    asChild?: boolean;
  };

export function Button({
  variant,
  size,
  iconOnly,
  fullWidth,
  icon,
  loading = false,
  asChild = false,
  disabled,
  type = "button",
  children,
  ...props
}: ButtonProps) {
  const className = buttonVariants({ variant, size, iconOnly, fullWidth });

  if (asChild) {
    return (
      <Slot.Root className={className} {...props}>
        {children}
      </Slot.Root>
    );
  }

  return (
    <button type={type} disabled={disabled || loading} aria-busy={loading || undefined} className={className} {...props}>
      {loading ? <Icon name="spinner" className="fa-spin" /> : icon ? <Icon name={icon} /> : null}
      {!iconOnly && children}
    </button>
  );
}
