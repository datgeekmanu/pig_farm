import { addDays, differenceInCalendarDays, formatISO, parseISO, startOfDay } from "date-fns"

/** ISO calendar date, e.g. "2026-10-22". The app never stores times. */
export type IsoDate = string

export const today = () => startOfDay(new Date())

export const toIsoDate = (date: Date): IsoDate => formatISO(date, { representation: "date" })

export const fromIsoDate = (iso: IsoDate) => parseISO(iso)

export const daysFromToday = (offset: number) => toIsoDate(addDays(today(), offset))

export const addDaysTo = (iso: IsoDate, days: number) => addDays(fromIsoDate(iso), days)

export const daysSince = (iso: IsoDate) => differenceInCalendarDays(today(), fromIsoDate(iso))
