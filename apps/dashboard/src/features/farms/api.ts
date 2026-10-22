import { db, respond } from "@/mocks/db"

// TODO(#1.4): GET /farms once the farm switcher endpoint exists.
export const farmsApi = {
  list: () => respond(db.farms()),
}
