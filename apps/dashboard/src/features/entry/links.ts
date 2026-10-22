import type { FlowKey } from "./types"

/** URL of a guided entry, with answers pre-filled from query params. */
export function entryHref(flow: FlowKey, prefill: Record<string, string> = {}) {
  const query = new URLSearchParams(prefill).toString()
  return `/entry/${flow}${query ? `?${query}` : ""}`
}
