import { useRouter } from "next/navigation"
import { useMutation, useQueryClient } from "@tanstack/react-query"
import { toast } from "sonner"
import { useActiveFarm } from "@/features/farms/active-farm"
import { useStock } from "@/features/health/queries"
import { useLots } from "@/features/lots/queries"
import { useSows } from "@/features/reproduction/queries"
import { entryApi, type EntryInput } from "./api"
import type { FlowContext } from "./types"

/** Everything a guided entry may ask about. Undefined while loading. */
export function useFlowContext(): FlowContext | undefined {
  const { farm } = useActiveFarm()
  const lots = useLots().data
  const sows = useSows().data
  const stock = useStock().data
  if (!lots || !sows || !stock) return undefined
  return { lots, sows, stock, currency: farm.currency }
}

export function useSubmitEntry() {
  const queryClient = useQueryClient()
  const router = useRouter()
  const { farm } = useActiveFarm()
  const refreshFarm = () => queryClient.invalidateQueries({ queryKey: [farm.id] })

  const undo = useMutation({
    mutationFn: entryApi.undo,
    onSuccess: async () => {
      await refreshFarm()
      toast.info("Saisie annulée", { description: "Les données sont revenues à leur état précédent." })
    },
  })

  return useMutation({
    mutationFn: (input: Omit<EntryInput, "farmId" | "currency">) => entryApi.submit({ ...input, farmId: farm.id, currency: farm.currency }),
    onSuccess: async ({ title, description }) => {
      await refreshFarm()
      router.back()
      toast.success(title, {
        description,
        duration: 10_000,
        action: { label: "Annuler", onClick: () => undo.mutate() },
      })
    },
  })
}
