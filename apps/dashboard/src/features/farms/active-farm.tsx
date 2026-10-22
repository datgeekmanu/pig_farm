"use client"

import { createContext, use, useState } from "react"
import { useQuery } from "@tanstack/react-query"
import { Spinner } from "@/components/ui/spinner"
import { activeFarm } from "@/lib/farm"
import { queryKeys } from "@/lib/query-keys"
import { farmsApi } from "./api"
import type { Farm } from "./types"

type ActiveFarmContext = {
  farm: Farm
  farms: Farm[]
  selectFarm: (id: string) => void
}

const Context = createContext<ActiveFarmContext | null>(null)

/** Loads the user's farms and exposes the one every screen is scoped to. */
export function ActiveFarmProvider({ children }: { children: React.ReactNode }) {
  const { data: farms } = useQuery({ queryKey: queryKeys.farms.all(), queryFn: farmsApi.list })
  const [farmId, setFarmId] = useState(activeFarm.get)

  if (!farms) {
    return (
      <div className="flex min-h-screen items-center justify-center">
        <Spinner />
      </div>
    )
  }

  const farm = farms.find((f) => f.id === farmId) ?? farms[0]
  const selectFarm = (id: string) => {
    activeFarm.set(id) // sent as X-Farm-Id on every API call
    setFarmId(id)
  }

  return <Context value={{ farm, farms, selectFarm }}>{children}</Context>
}

export function useActiveFarm() {
  const context = use(Context)
  if (!context) throw new Error("useActiveFarm must be used inside <ActiveFarmProvider>")
  return context
}
