import { cookies } from "next/headers"
import { getRequestConfig } from "next-intl/server"
import { defaultLocale, isLocale, LOCALE_COOKIE } from "./config"

// No locale prefix in URLs: the active locale lives in a cookie.
export default getRequestConfig(async () => {
  const value = (await cookies()).get(LOCALE_COOKIE)?.value
  const locale = isLocale(value) ? value : defaultLocale

  return {
    locale,
    messages: (await import(`../../messages/${locale}.json`)).default,
  }
})
