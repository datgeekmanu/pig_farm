import { useQuery } from "@tanstack/react-query"
import type { Tone } from "@/components/ui/badge"
import { entryHref } from "@/features/entry/links"
import { useActiveFarm } from "@/features/farms/active-farm"
import { useStock } from "@/features/health/queries"
import type { StockItem } from "@/features/health/types"
import { useLots } from "@/features/lots/queries"
import type { Lot } from "@/features/lots/types"
import { gestationOf, isGestating, type Gestation } from "@/features/reproduction/gestation"
import { useSows } from "@/features/reproduction/queries"
import type { Sow } from "@/features/reproduction/types"
import { formatDate, formatNumber } from "@/lib/format"
import { queryKeys } from "@/lib/query-keys"
import { tasksApi } from "./api"
import { useReminderSettings, type ReminderCategory } from "./reminder-settings"

export type TodayTask = {
  id: string
  category: ReminderCategory
  tone: Tone
  icon: string
  tag: string
  title: string
  description: string
  /** Guided entry that completes the task, pre-filled. */
  href: string
}

// TODO(#8.1): the scheduler will compute these server-side; derived client-side for the demo.

export function stockTasks(stock: StockItem[]): TodayTask[] {
  const feed = stock.find((item) => item.key === "crois")
  if (!feed || feed.quantity >= feed.alertThreshold) return []
  const id = "low-grower-feed"
  return [
    {
      id,
      category: "stock",
      tone: "warning",
      icon: "warehouse",
      tag: "Stock",
      title: "Aliment croissance bas",
      description: `${feed.location} : ${formatNumber(feed.quantity)} kg, environ ${Math.floor(feed.quantity / feed.dailyUsage)} jours au rythme actuel`,
      href: entryHref("stock-in", { item: feed.key, task: id }),
    },
  ]
}

export function careTasks(lots: Lot[]): TodayTask[] {
  const lot = lots[3] ?? lots[0]
  if (!lot) return []
  const id = `ppc-${lot.id}`
  return [
    {
      id,
      category: "care",
      tone: "danger",
      icon: "syringe",
      tag: "Santé",
      title: `Rappel vaccin PPC · ${lot.id}`,
      description: `Prévu demain · ${formatNumber(lot.headcount)} animaux`,
      href: entryHref("treatment", { lot: lot.id, product: "Vaccin PPC", dose: "2", task: id }),
    },
  ]
}

function gestationTask({ sow, isDueSoon, dueDate, day, stage }: Gestation): TodayTask | null {
  if (isDueSoon) {
    return {
      id: `farrowing-${sow.id}`,
      category: "reproduction",
      tone: "info",
      icon: "calendar",
      tag: "Repro",
      title: `Mise bas prévue · ${sow.id}`,
      description: `${formatDate(dueDate)} · passage en maternité`,
      href: entryHref("farrowing", { sow: sow.id }),
    }
  }
  if (stage.tone !== "danger") return null
  return {
    id: `ultrasound-${sow.id}`,
    category: "reproduction",
    tone: "warning",
    icon: "triangle-exclamation",
    tag: "Repro",
    title: `Écho en retard · ${sow.id}`,
    description: `Saillie il y a ${day} jours, échographie non saisie`,
    href: entryHref("ultrasound", { sow: sow.id }),
  }
}

export function reproductionTasks(sows: Sow[]): TodayTask[] {
  const gestations = sows.filter(isGestating).map(gestationOf).map(gestationTask)
  const toServe = sows
    .filter((sow) => sow.status === "empty")
    .map<TodayTask>((sow) => ({
      id: `service-${sow.id}`,
      category: "reproduction",
      tone: "info",
      icon: "venus",
      tag: "Repro",
      title: `À saillir · ${sow.id}`,
      description: sow.note ?? "Truie vide",
      href: entryHref("service", { sow: sow.id }),
    }))
  return [...gestations.filter((task) => task !== null), ...toServe]
}

/** Tasks for today, filtered by the reminder toggles and tasks already done. */
export function useTodayTasks(): TodayTask[] {
  const { farm } = useActiveFarm()
  const { enabled } = useReminderSettings()
  const lots = useLots().data ?? []
  const sows = useSows().data ?? []
  const stock = useStock().data ?? []
  const done = useQuery({ queryKey: queryKeys.doneTasks.all(farm.id), queryFn: tasksApi.done }).data ?? []

  return [...stockTasks(stock), ...careTasks(lots), ...reproductionTasks(sows)].filter(
    (task) => enabled[task.category] && !done.includes(task.id),
  )
}
