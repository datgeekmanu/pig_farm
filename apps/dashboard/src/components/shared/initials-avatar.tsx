import { cva, type VariantProps } from "class-variance-authority"

const avatarVariants = cva("flex flex-none items-center justify-center rounded-full font-semibold text-white", {
  variants: {
    color: {
      petrol: "bg-avatar-petrol",
      teal: "bg-avatar-teal",
      forest: "bg-avatar-forest",
      earth: "bg-avatar-earth",
      neutral: "bg-avatar-neutral",
    },
    size: {
      md: "size-12 text-body",
      sm: "size-10 text-caption font-bold",
    },
  },
  defaultVariants: { color: "petrol", size: "md" },
})

export type AvatarColor = NonNullable<VariantProps<typeof avatarVariants>["color"]>

/** Rotating colours for lot lists, so neighbouring lots are easy to tell apart. */
export const LOT_COLORS: AvatarColor[] = ["petrol", "teal", "forest", "earth"]

type InitialsAvatarProps = VariantProps<typeof avatarVariants> & { children: React.ReactNode }

/** Coloured circle holding a short code (lot suffix, sow number) or an icon. */
export function InitialsAvatar({ color, size, children }: InitialsAvatarProps) {
  return <span className={avatarVariants({ color, size })}>{children}</span>
}
