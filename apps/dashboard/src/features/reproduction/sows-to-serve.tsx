import Link from "next/link"
import { Badge } from "@/components/ui/badge"
import { Card, CardHeader } from "@/components/ui/card"
import { entryHref } from "@/features/entry/links"
import type { Sow } from "./types"

export function SowsToServe({ sows }: { sows: Sow[] }) {
  return (
    <Card>
      <CardHeader title="À saillir" description="Truies vides et retours en chaleur" icon="venus" />
      <div className="flex flex-col">
        {sows.map((sow) => (
          <Link
            key={sow.id}
            href={entryHref("service", { sow: sow.id })}
            title="Saisir la saillie"
            className="flex items-center justify-between gap-3 border-t py-2.5 transition-colors hover:bg-hover-overlay"
          >
            <div className="flex flex-col">
              <span className="text-body font-semibold">
                {sow.id} · {sow.name}
              </span>
              <span className="text-caption text-muted-foreground">{sow.note}</span>
            </div>
            <Badge tone="warning" size="sm">
              À saillir
            </Badge>
          </Link>
        ))}
      </div>
    </Card>
  )
}
