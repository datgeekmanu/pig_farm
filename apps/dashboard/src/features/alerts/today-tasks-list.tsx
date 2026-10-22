"use client"

import Link from "next/link"
import { Badge } from "@/components/ui/badge"
import { SectionHeader } from "@/components/shared/section-header"
import { useTodayTasks } from "./today-tasks"

export function TodayTasksList() {
  const tasks = useTodayTasks()

  return (
    <section className="flex flex-col">
      <SectionHeader className="mb-2" title="À faire aujourd’hui" aside="Touchez une tâche pour la saisir" />
      <div className="flex flex-col gap-2">
        {tasks.map((task) => (
          <Link
            key={task.id}
            href={task.href}
            className="flex items-start gap-3 rounded-lg border bg-card px-4 py-3.5 transition-colors hover:border-ring"
          >
            <span className="flex min-w-0 flex-1 flex-col">
              <span className="text-body font-semibold">{task.title}</span>
              <span className="text-caption text-muted-foreground">{task.description}</span>
            </span>
            <Badge tone={task.tone} icon={task.icon}>
              {task.tag}
            </Badge>
          </Link>
        ))}
        {tasks.length === 0 && <p className="py-4 text-sm text-muted-foreground">Tout est fait pour aujourd’hui.</p>}
      </div>
    </section>
  )
}
