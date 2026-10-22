import { toast } from "sonner"
import { useActiveFarm } from "@/features/farms/active-farm"

// TODO(#9.1): call the Laravel PDF endpoint and download the file.
export function usePdfExport() {
  const { farm } = useActiveFarm()
  return () => toast.success("Rapport PDF généré", { description: `Le document a été préparé avec l’en-tête de ${farm.name}.` })
}
