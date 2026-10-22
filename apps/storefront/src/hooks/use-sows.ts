import { useQuery } from "@tanstack/react-query";
import { sows } from "@/lib/mock-data";
import { queryKeys } from "@/lib/query-keys";

// TODO(#3.1, #3.3): replace mock queryFns with api.get("/sows").
export function useSows() {
  return useQuery({
    queryKey: queryKeys.sows.all(),
    queryFn: async () => sows,
  });
}

export function useSow(id: string) {
  return useQuery({
    queryKey: queryKeys.sows.detail(id),
    queryFn: async () => {
      const sow = sows.find((s) => s.id === id);
      if (!sow) throw new Error(`Truie ${id} introuvable`);
      return sow;
    },
  });
}
