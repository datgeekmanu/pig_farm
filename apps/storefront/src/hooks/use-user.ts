import { useQuery } from "@tanstack/react-query";
import { api } from "@/lib/api-client";
import { queryKeys } from "@/lib/query-keys";

// Pattern for every resource hook: one file per resource, keys from query-keys.ts.
export interface User {
  id: number;
  name: string;
  email: string;
}

export function useUser() {
  return useQuery({
    queryKey: queryKeys.auth.user(),
    queryFn: () => api.get<User>("/user"),
  });
}
