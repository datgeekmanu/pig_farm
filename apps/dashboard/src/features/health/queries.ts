import { useQuery } from "@tanstack/react-query"
import { useActiveFarm } from "@/features/farms/active-farm"
import { queryKeys } from "@/lib/query-keys"
import { healthApi } from "./api"

export function useStock() {
  const { farm } = useActiveFarm()
  return useQuery({ queryKey: queryKeys.stock.all(farm.id), queryFn: healthApi.stock })
}
