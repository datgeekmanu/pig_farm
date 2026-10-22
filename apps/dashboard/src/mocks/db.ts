// In-memory stand-in for the Laravel API until the real endpoints land.
// Every write takes a snapshot first so the last entry can be undone.
import { FARMS, LOTS, SALES, SOWS, STOCK } from "./seed"
import type { Lot, LotEvent } from "@/features/lots/types"

type State = {
  lots: Record<string, Lot[]>
  events: LotEvent[]
  sows: typeof SOWS
  stock: typeof STOCK
  sales: typeof SALES
  /** "À faire aujourd'hui" tasks completed through a guided entry. */
  doneTasks: string[]
}

const initialState = (): State => structuredClone({ lots: LOTS, events: [], sows: SOWS, stock: STOCK, sales: SALES, doneTasks: [] })

let state = initialState()
let snapshot: State | null = null

export const db = {
  farms: () => FARMS,
  read: () => state,
  /** Applies a mutation and remembers the previous state for undo. */
  write<T>(mutate: (draft: State) => T): T {
    snapshot = structuredClone(state)
    const draft = structuredClone(state)
    const result = mutate(draft)
    state = draft
    return result
  },
  undo() {
    if (!snapshot) return false
    state = snapshot
    snapshot = null
    return true
  },
}

/** Simulates network latency so loading states stay visible in dev. */
export function respond<T>(value: T, ms = 250): Promise<T> {
  return new Promise((resolve) => setTimeout(() => resolve(structuredClone(value)), ms))
}
