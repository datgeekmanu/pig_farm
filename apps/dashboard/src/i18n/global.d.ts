import type messages from "../../messages/fr.json"
import type { Locale } from "./config"

// Type-safe message keys: t("common.save") is checked against fr.json.
declare module "next-intl" {
  interface AppConfig {
    Locale: Locale
    Messages: typeof messages
  }
}
