"use client"

import { AppShell } from "@/components/app-shell/app-shell"
import { AuthGuard } from "@/components/auth-guard"
import { ReminderSettingsProvider } from "@/features/alerts/reminder-settings"
import { ActiveFarmProvider } from "@/features/farms/active-farm"

export default function AppLayout({ children }: LayoutProps<"/">) {
  return (
    <AuthGuard>
      <ActiveFarmProvider>
        <ReminderSettingsProvider>
          <AppShell>{children}</AppShell>
        </ReminderSettingsProvider>
      </ActiveFarmProvider>
    </AuthGuard>
  )
}
