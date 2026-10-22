import { useQuery } from "@tanstack/react-query"
import { useActiveFarm } from "@/features/farms/active-farm"
import { queryKeys } from "@/lib/query-keys"
import { financeApi } from "./api"

export function useSales() {
  const { farm } = useActiveFarm()
  return useQuery({ queryKey: queryKeys.sales.all(farm.id), queryFn: financeApi.sales })
}
