// Active farm id, sent as X-Farm-Id on every request (see ResolveFarm middleware).
const KEY = "farmId"

export const activeFarm = {
  get: (): string | null => (typeof window === "undefined" ? null : localStorage.getItem(KEY)),
  set: (id: string) => localStorage.setItem(KEY, id),
  clear: () => localStorage.removeItem(KEY),
}
