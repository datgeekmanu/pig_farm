"use client"

import { useEffect } from "react"
import { useRouter } from "next/navigation"
import { Spinner } from "@/components/ui/spinner"
import { useMe } from "@/features/auth/queries"

// Client-side check against /user. proxy.ts only checks cookie presence;
// this confirms the session is actually valid.
export function AuthGuard({ children }: { children: React.ReactNode }) {
  const router = useRouter()
  const { data: user, isPending, isError } = useMe()

  useEffect(() => {
    if (isError) router.replace("/login")
  }, [isError, router])

  if (isPending) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <Spinner />
      </div>
    )
  }

  if (isError || !user) return null

  return children
}
