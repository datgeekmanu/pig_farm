import { MutationCache, QueryCache, QueryClient } from "@tanstack/react-query"
import { toast } from "sonner"
import { ApiError } from "./api-client"
import { deleteAuthCookies } from "./auth-actions"
import { activeFarm } from "./farm"

declare module "@tanstack/react-query" {
  interface Register {
    defaultError: ApiError
    // Opt out of the global error toast: useMutation({ meta: { silent: true } })
    queryMeta: { silent?: boolean }
    mutationMeta: { silent?: boolean }
  }
}

async function handleUnauthenticated() {
  activeFarm.clear()
  queryClient.clear()
  await deleteAuthCookies()
  // Hard navigation on purpose: drops all in-memory client state.
  // eslint-disable-next-line @next/next/no-location-assign-relative-destination
  window.location.href = "/login"
}

function handleGlobalError(error: unknown, meta?: { silent?: boolean }) {
  if (!(error instanceof ApiError)) return
  if (error.status === 401) return void handleUnauthenticated()
  if (meta?.silent) return

  // 422: forms map field errors themselves; toast the summary only.
  toast.error(error.message)
}

export const queryClient = new QueryClient({
  queryCache: new QueryCache({
    onError: (error, query) => handleGlobalError(error, query.meta),
  }),
  mutationCache: new MutationCache({
    onError: (error, _vars, _ctx, mutation) => handleGlobalError(error, mutation.meta),
  }),
  defaultOptions: {
    queries: {
      staleTime: 60 * 1000,
      retry: (count, error) => {
        if (error instanceof ApiError && error.status < 500) return false
        return count < 2
      },
    },
  },
})
