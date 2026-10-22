// Query key factory. Farm-scoped keys start with the farm id, so switching
// farm can never serve another farm's cached data.
export const queryKeys = {
  auth: {
    me: () => ["auth", "me"] as const,
  },
  farms: {
    all: () => ["farms"] as const,
  },
  lots: {
    all: (farmId: string) => [farmId, "lots"] as const,
    events: (farmId: string, lotId: string) => [farmId, "lots", lotId, "events"] as const,
  },
  sows: {
    all: (farmId: string) => [farmId, "sows"] as const,
  },
  stock: {
    all: (farmId: string) => [farmId, "stock"] as const,
  },
  doneTasks: {
    all: (farmId: string) => [farmId, "done-tasks"] as const,
  },
  sales: {
    all: (farmId: string) => [farmId, "sales"] as const,
  },
}
