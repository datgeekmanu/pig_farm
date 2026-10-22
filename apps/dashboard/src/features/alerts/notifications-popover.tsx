"use client"

import Link from "next/link"
import { useState } from "react"
import { Icon } from "@/components/icon"
import { TONE_TEXT } from "@/components/ui/badge"
import { Button } from "@/components/ui/button"
import { Popover, PopoverContent, PopoverTrigger } from "@/components/ui/popover"
import { cn } from "@/lib/utils"
import { useTodayTasks } from "./today-tasks"

export function NotificationsPopover() {
  const [open, setOpen] = useState(false)
  const tasks = useTodayTasks()

  return (
    <Popover open={open} onOpenChange={setOpen}>
      <PopoverTrigger asChild>
        <Button variant="ghost" size="icon" aria-label="Notifications" className="relative">
          <Icon name="bell" />
          {tasks.length > 0 && (
            <span className="pointer-events-none absolute top-1 right-1 flex h-4 min-w-4 items-center justify-center rounded-full bg-highlight px-1 text-[10px] font-bold text-highlight-foreground">
              {tasks.length}
            </span>
          )}
        </Button>
      </PopoverTrigger>
      <PopoverContent align="end" sideOffset={8} className="w-95 gap-0 rounded-lg p-2">
        <div className="flex items-baseline justify-between px-2.5 pt-2 pb-2.5">
          <span className="font-heading text-base font-bold">Notifications</span>
          <span className="text-caption text-muted-foreground">{tasks.length} non lues</span>
        </div>
        {tasks.map((task) => (
          <Link
            key={task.id}
            href={task.href}
            onClick={() => setOpen(false)}
            className="flex items-start gap-3 rounded-md p-2.5 transition-colors hover:bg-hover-overlay"
          >
            <Icon name={task.icon} className={cn("mt-0.75", TONE_TEXT[task.tone])} />
            <span className="flex flex-1 flex-col">
              <span className="text-sm font-semibold">{task.title}</span>
              <span className="text-caption text-muted-foreground">{task.description}</span>
            </span>
          </Link>
        ))}
      </PopoverContent>
    </Popover>
  )
}
