import { useQuery } from "@tanstack/react-query";
import { farmSummary, tasks } from "@/lib/mock-data";
import { queryKeys } from "@/lib/query-keys";

// TODO(#1.4, #8.2): replace mock queryFns with api.get once the endpoints exist.
export function useFarmSummary() {
  return useQuery({
    queryKey: queryKeys.farm.summary(),
    queryFn: async () => farmSummary,
  });
}

export function useFarmTasks() {
  return useQuery({
    queryKey: queryKeys.farm.tasks(),
    queryFn: async () => tasks,
  });
}

export function useCurrency() {
  return useFarmSummary().data?.currency ?? "XOF";
}
