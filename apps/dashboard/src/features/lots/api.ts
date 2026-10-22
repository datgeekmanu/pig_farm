import { db, respond } from "@/mocks/db"
import { BUILDINGS } from "@/mocks/seed"

// TODO(#2.1): GET /lots, GET /lots/{id}/events (farm comes from X-Farm-Id).
export const lotsApi = {
  list: (farmId: string) => respond(db.read().lots[farmId] ?? []),
  events: (lotId: string) => respond(db.read().events.filter((event) => event.lotId === lotId)),
  buildings: () => BUILDINGS,
}
