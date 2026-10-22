import type { Farm } from "@pig-farm/types";

export type Currency = Farm["currency"];

// Fixed EUR peg of the CFA franc.
const XOF_PER_EUR = 655.957;

const LOCALE = "fr-FR";

export function formatNumber(value: number, digits = 1) {
  return value.toLocaleString(LOCALE, { minimumFractionDigits: digits, maximumFractionDigits: digits });
}

export function formatInteger(value: number) {
  return Math.round(value).toLocaleString(LOCALE);
}

export function formatKg(value: number) {
  return `${formatNumber(value)} kg`;
}

export function formatPercent(value: number, digits = 1) {
  return `${formatNumber(value, digits)} %`;
}

// Amounts are stored in XOF (FCFA), displayed in the farm's currency.
export function formatMoney(amountXof: number, currency: Currency) {
  if (currency === "EUR") return `${formatNumber(amountXof / XOF_PER_EUR, 2)} €`;
  return `${formatInteger(amountXof)} FCFA`;
}

export function formatShortDate(date: Date) {
  return date.toLocaleDateString(LOCALE, { weekday: "short", day: "numeric", month: "short" });
}

export function formatDayMonth(date: Date) {
  return date.toLocaleDateString(LOCALE, { day: "numeric", month: "short" });
}

export function formatLongDate(date: Date) {
  return date.toLocaleDateString(LOCALE, { weekday: "short", day: "numeric", month: "long" });
}
