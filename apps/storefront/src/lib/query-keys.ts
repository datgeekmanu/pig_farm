// Single source of truth for TanStack Query keys. Add one block per API resource:
//   resource: { all: () => [...], list: (params) => [...], detail: (id) => [...] }
export const queryKeys = {
  auth: {
    user: () => ["auth", "user"] as const,
  },
  farm: {
    summary: () => ["farm", "summary"] as const,
    tasks: () => ["farm", "tasks"] as const,
  },
  lots: {
    all: () => ["lots"] as const,
    detail: (id: string) => ["lots", id] as const,
  },
  sows: {
    all: () => ["sows"] as const,
    detail: (id: string) => ["sows", id] as const,
  },
  listings: {
    all: () => ["listings"] as const,
    detail: (lotId: string) => ["listings", lotId] as const,
  },
};
