import { useRouter } from "next/navigation"
import { useMutation, useQuery, useQueryClient } from "@tanstack/react-query"
import { deleteAuthCookies } from "@/lib/auth-actions"
import { activeFarm } from "@/lib/farm"
import { queryKeys } from "@/lib/query-keys"
import { authApi, type LoginInput } from "./api"

export const useMe = () =>
  useQuery({
    queryKey: queryKeys.auth.me(),
    queryFn: authApi.me,
    meta: { silent: true },
  })

export const useLogin = () => {
  const queryClient = useQueryClient()
  return useMutation({
    mutationFn: (input: LoginInput) => authApi.login(input),
    onSuccess: (user) => queryClient.setQueryData(queryKeys.auth.me(), user),
    meta: { silent: true },
  })
}

export const useLogout = () => {
  const queryClient = useQueryClient()
  const router = useRouter()
  return useMutation({
    mutationFn: authApi.logout,
    onSettled: async () => {
      queryClient.clear()
      activeFarm.clear()
      await deleteAuthCookies()
      router.replace("/login")
    },
  })
}

/** Finance and sales are owner-only. */
export const useIsOwner = () => useMe().data?.role === "owner"
