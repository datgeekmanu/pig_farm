import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query";
import { mean } from "@/lib/herd";
import { lots } from "@/lib/mock-data";
import type { Lot } from "@/lib/models";
import { queryKeys } from "@/lib/query-keys";

// TODO(#2.1, #2.3): replace mock queryFns with api.get("/lots") / api.post("/lots/:id/weighings").
export function useLots() {
  return useQuery({
    queryKey: queryKeys.lots.all(),
    queryFn: async () => lots,
  });
}

export function useLot(id: string) {
  return useQuery({
    queryKey: queryKeys.lots.detail(id),
    queryFn: async () => {
      const lot = lots.find((l) => l.id === id);
      if (!lot) throw new Error(`Lot ${id} introuvable`);
      return lot;
    },
  });
}

export function useRecordWeight(lotId: string) {
  const queryClient = useQueryClient();

  return useMutation({
    mutationFn: async (weightKg: number) => weightKg,
    onSuccess: (weightKg) => {
      queryClient.setQueryData<Lot>(queryKeys.lots.detail(lotId), (lot) => {
        if (!lot) return lot;
        const weights = [...lot.weights, weightKg];
        const history = [...lot.history];
        history[history.length - 1] = { ...history[history.length - 1], averageKg: mean(weights) };
        return { ...lot, weights, history };
      });
    },
  });
}
