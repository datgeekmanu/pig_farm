import { useQuery } from "@tanstack/react-query"
import { useActiveFarm } from "@/features/farms/active-farm"
import { queryKeys } from "@/lib/query-keys"
import { sowsApi } from "./api"

export function useSows() {
  const { farm } = useActiveFarm()
  return useQuery({ queryKey: queryKeys.sows.all(farm.id), queryFn: sowsApi.list })
}
