import { useMe } from "@/features/auth/queries"

export function UserAvatar() {
  const user = useMe().data
  const initials = user ? `${user.firstName[0]}${user.lastName[0]}` : ""

  return (
    <span className="flex size-11 flex-none items-center justify-center rounded-full border-2 border-white bg-earth-soft text-sm font-bold text-earth-strong">
      {initials}
    </span>
  )
}
