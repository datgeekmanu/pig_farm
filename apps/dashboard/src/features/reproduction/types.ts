import type { IsoDate } from "@/lib/dates"

export type SowStatus = "gestating" | "empty" | "lactating"

export type Sow = {
  id: string
  name: string
  status: SowStatus
  /** Service (saillie) date, gestating sows only. */
  servedOn?: IsoDate
  boar?: string
  ultrasoundConfirmed?: boolean
  note?: string
}
