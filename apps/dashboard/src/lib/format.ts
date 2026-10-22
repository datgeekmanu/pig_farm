import { format } from "date-fns"
import { fr } from "date-fns/locale"
import type { Currency } from "@/features/farms/types"

/** fr-FR number: non-breaking thousands, decimal comma. */
export function formatNumber(value: number, decimals = 0) {
  return new Intl.NumberFormat("fr-FR", {
    minimumFractionDigits: decimals,
    maximumFractionDigits: decimals,
  }).format(value)
}

// Amounts are stored in FCFA (XOF); EUR farms see them converted at the fixed peg.
const XOF_PER_EUR = 655.957

/** Converts an amount typed in the farm's currency to FCFA for storage. */
export const toXof = (amount: number, currency: Currency) => (currency === "EUR" ? amount * XOF_PER_EUR : amount)

export function formatMoney(amountXof: number, currency: Currency) {
  return currency === "EUR" ? `${formatNumber(amountXof / XOF_PER_EUR)} €` : `${formatNumber(amountXof)} FCFA`
}

/** Compact form for large totals: "11,2 M FCFA" / "17,1 k€". */
export function formatMoneyCompact(amountXof: number, currency: Currency) {
  return currency === "EUR" ? `${formatNumber(amountXof / XOF_PER_EUR / 1000, 1)} k€` : `${formatNumber(amountXof / 1_000_000, 1)} M FCFA`
}

export function currencyLabel(currency: Currency) {
  return currency === "EUR" ? "€" : "FCFA"
}

const DATE_PRESETS = {
  short: "EEE d MMM", // jeu. 22 oct.
  dayMonth: "d MMM", // 22 oct.
  long: "EEEE d MMMM", // jeudi 22 octobre
  full: "EEEE d MMMM yyyy",
  numeric: "dd.MM", // 22.10
  weekday: "EEE",
} as const

export type DatePreset = keyof typeof DATE_PRESETS

export function formatDate(date: Date, preset: DatePreset = "short") {
  return format(date, DATE_PRESETS[preset], { locale: fr })
}

/** Parses user input like "15 960" or "2,5". Returns 0 when not a number. */
export function parseNumber(value: string | number | undefined) {
  const parsed = parseFloat(
    String(value ?? "")
      .replace(/\s/g, "")
      .replace(",", "."),
  )
  return Number.isNaN(parsed) ? 0 : parsed
}
