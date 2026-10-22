"use client"

import { createContext, use, useState } from "react"

export type ReminderCategory = "care" | "reproduction" | "stock"

export const REMINDER_CATEGORIES: { key: ReminderCategory; label: string; icon: string }[] = [
  { key: "care", label: "Vaccins et traitements", icon: "syringe" },
  { key: "reproduction", label: "Reproduction", icon: "venus-mars" },
  { key: "stock", label: "Stocks bas", icon: "warehouse" },
]

type ReminderSettings = {
  enabled: Record<ReminderCategory, boolean>
  setEnabled: (category: ReminderCategory, on: boolean) => void
}

const Context = createContext<ReminderSettings | null>(null)

// TODO(#7.3): persist with the notification settings endpoint.
export function ReminderSettingsProvider({ children }: { children: React.ReactNode }) {
  const [enabled, setAll] = useState<Record<ReminderCategory, boolean>>({ care: true, reproduction: true, stock: true })
  const setEnabled = (category: ReminderCategory, on: boolean) => setAll((prev) => ({ ...prev, [category]: on }))

  return <Context value={{ enabled, setEnabled }}>{children}</Context>
}

export function useReminderSettings() {
  const context = use(Context)
  if (!context) throw new Error("useReminderSettings must be used inside <ReminderSettingsProvider>")
  return context
}
