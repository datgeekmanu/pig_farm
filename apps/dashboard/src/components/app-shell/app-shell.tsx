"use client"

import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Sheet, SheetContent, SheetTitle, SheetTrigger } from "@/components/ui/sheet"
import { AppSidebar } from "./app-sidebar"
import { HeaderActions } from "./header-actions"
import { MainNav } from "./main-nav"

export function AppShell({ children }: { children: React.ReactNode }) {
  return (
    <div className="flex h-screen gap-4 overflow-hidden p-4 max-sm:p-0">
      <div className="max-lg:hidden">
        <AppSidebar />
      </div>

      <main className="relative flex min-w-0 flex-1 flex-col overflow-hidden">
        <header className="flex min-h-17 flex-none flex-wrap items-center gap-4 py-1 pr-6 pl-7 max-sm:px-4">
          <MobileSidebar />
          <MainNav />
          <HeaderActions />
        </header>
        <div className="flex-1 overflow-auto px-7 pt-3 pb-10 max-sm:px-4">
          <div className="mx-auto flex max-w-7xl flex-col gap-7">{children}</div>
        </div>
      </main>
    </div>
  )
}

/** Below 1024px the widget sidebar collapses behind a menu button. */
function MobileSidebar() {
  return (
    <Sheet>
      <SheetTrigger asChild>
        <Button variant="outline" size="icon" aria-label="Ouvrir le menu" className="lg:hidden">
          <Icon name="bars" />
        </Button>
      </SheetTrigger>
      <SheetContent side="left" className="w-auto border-0 bg-transparent p-2 shadow-none">
        <SheetTitle className="sr-only">Menu</SheetTitle>
        <AppSidebar />
      </SheetContent>
    </Sheet>
  )
}
