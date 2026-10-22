import { db, respond } from "@/mocks/db"

// TODO(#3.1): GET /sows.
export const sowsApi = {
  list: () => respond(db.read().sows),
}
