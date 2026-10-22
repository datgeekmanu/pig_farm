import { useQuery } from "@tanstack/react-query"
import { useActiveFarm } from "@/features/farms/active-farm"
import { queryKeys } from "@/lib/query-keys"
import { lotsApi } from "./api"

export function useLots() {
  const { farm } = useActiveFarm()
  return useQuery({ queryKey: queryKeys.lots.all(farm.id), queryFn: () => lotsApi.list(farm.id) })
}

export function useLot(lotId: string) {
  const query = useLots()
  return { ...query, data: query.data?.find((lot) => lot.id === lotId) }
}

export function useLotEvents(lotId: string) {
  const { farm } = useActiveFarm()
  return useQuery({ queryKey: queryKeys.lots.events(farm.id, lotId), queryFn: () => lotsApi.events(lotId) })
}
