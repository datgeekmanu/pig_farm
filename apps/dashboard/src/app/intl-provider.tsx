import { NextIntlClientProvider } from "next-intl"

// Reads the locale cookie (request data), so it must render inside <Suspense>
// under cacheComponents. Messages come from src/i18n/request.ts.
export async function IntlProvider({ children }: { children: React.ReactNode }) {
  return <NextIntlClientProvider>{children}</NextIntlClientProvider>
}
