"use client"

import Link from "next/link"
import { usePathname } from "next/navigation"
import { useIsOwner } from "@/features/auth/queries"
import { cn } from "@/lib/utils"
import { isActive, NAV_ITEMS } from "./nav-items"

/** Top text tabs: active = bold + underline. */
export function MainNav() {
  const pathname = usePathname()
  const isOwner = useIsOwner()

  return (
    <nav aria-label="Navigation principale" className="flex min-w-0 flex-[1_1_520px] flex-wrap gap-x-7 gap-y-2">
      {NAV_ITEMS.filter((item) => isOwner || !item.ownerOnly).map((item) => {
        const active = isActive(pathname, item.href)
        return (
          <Link
            key={item.href}
            href={item.href}
            aria-current={active ? "page" : undefined}
            className={cn(
              "flex-none border-b-2 py-2.5 text-nav transition-colors",
              active
                ? "border-foreground font-bold text-foreground"
                : "border-transparent font-medium text-muted-foreground hover:text-foreground",
            )}
          >
            {item.label}
          </Link>
        )
      })}
    </nav>
  )
}
