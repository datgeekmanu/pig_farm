import { Suspense } from "react"
import type { Metadata } from "next"
import { Geist_Mono, Manrope, Nunito_Sans } from "next/font/google"
import { ThemeProvider } from "@/components/theme-provider"
import { defaultLocale, LOCALE_COOKIE } from "@/i18n/config"
import { IntlProvider } from "./intl-provider"
import { Providers } from "./providers"
import "./globals.css"

// Manrope for headings and key figures, Nunito Sans for body and controls.
const display = Manrope({
  variable: "--font-display",
  subsets: ["latin"],
  weight: ["500", "600", "700"],
})

const body = Nunito_Sans({
  variable: "--font-body",
  subsets: ["latin"],
})

const geistMono = Geist_Mono({
  variable: "--font-geist-mono",
  subsets: ["latin"],
})

export const metadata: Metadata = {
  title: { default: "Pig Farm", template: "%s · Pig Farm" },
  description: "Gestion d'élevage porcin",
  icons: { icon: "/brand/pigfarm-mark-reversed.png" },
}

// Sets <html lang> from the locale cookie before paint, keeping the shell static.
const langScript = `(function(){try{var m=document.cookie.match(/(?:^|; )${LOCALE_COOKIE}=([^;]*)/);if(m)document.documentElement.lang=decodeURIComponent(m[1])}catch(e){}})()`

export default function RootLayout({ children }: LayoutProps<"/">) {
  return (
    <html
      lang={defaultLocale}
      className={`${display.variable} ${body.variable} ${geistMono.variable} h-full antialiased`}
      suppressHydrationWarning
    >
      <head>
        <script dangerouslySetInnerHTML={{ __html: langScript }} />
      </head>
      <body className="flex min-h-full flex-col">
        <ThemeProvider defaultTheme="system">
          <Suspense>
            <IntlProvider>
              <Providers>{children}</Providers>
            </IntlProvider>
          </Suspense>
        </ThemeProvider>
      </body>
    </html>
  )
}
