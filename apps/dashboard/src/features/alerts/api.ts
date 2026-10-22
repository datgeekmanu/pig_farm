import { db, respond } from "@/mocks/db"

// TODO(#8.2): GET /notifications (read state lives server-side).
export const tasksApi = {
  done: () => respond(db.read().doneTasks),
}
