import { IconTile } from "@/components/ui/card"
import { subDays } from "date-fns"
import { fromIsoDate, today } from "@/lib/dates"
import { formatDate, formatNumber } from "@/lib/format"
import type { Lot, LotEvent, Weighing } from "../types"

type HistoryEntry = { icon: string; text: string; date: Date }

type LotHistoryProps = { lot: Lot; events: LotEvent[]; lastWeighing: Weighing }

export function LotHistory({ lot, events, lastWeighing }: LotHistoryProps) {
  const entries: HistoryEntry[] = [
    ...events.map((event) => ({ icon: event.icon, text: event.text, date: fromIsoDate(event.date) })),
    // TODO(#2.3, #5.2): weighings and treatments come from their own endpoints.
    { icon: "weight-scale", text: `Pesée : ${formatNumber(lastWeighing.avgWeightKg, 1)} kg de moyenne`, date: lastWeighing.date },
    { icon: "syringe", text: "Ivermectine 1 % · 1 ml/33 kg · Jean Mvogo", date: subDays(today(), 9) },
    {
      icon: "arrow-right-to-bracket",
      text: `Mise en lot · ${formatNumber(lot.initialHeadcount)} animaux en loge ${lot.pen}`,
      date: fromIsoDate(lot.startDate),
    },
  ].sort((a, b) => b.date.getTime() - a.date.getTime())

  return (
    <section className="flex flex-col">
      <h2 className="mb-2 text-lg leading-6">Historique</h2>
      {entries.map((entry, i) => (
        <div key={i} className="flex items-start gap-3.5 border-t py-3">
          <IconTile name={entry.icon} />
          <span className="flex flex-1 flex-col">
            <span className="text-body font-semibold">{entry.text}</span>
            <span className="text-caption text-muted-foreground">{formatDate(entry.date, "long")}</span>
          </span>
        </div>
      ))}
    </section>
  )
}
