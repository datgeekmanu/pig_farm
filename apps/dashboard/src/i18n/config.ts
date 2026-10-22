const locales = ["fr", "en"] as const
export type Locale = (typeof locales)[number]

export const defaultLocale: Locale = "fr"
export const LOCALE_COOKIE = "NEXT_LOCALE"

export function isLocale(value: unknown): value is Locale {
  return locales.includes(value as Locale)
}
