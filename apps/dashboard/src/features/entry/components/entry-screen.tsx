"use client"

import Link from "next/link"
import { Icon } from "@/components/icon"
import { InitialsAvatar } from "@/components/shared/initials-avatar"
import { PageHeader } from "@/components/shared/page-header"
import { Spinner } from "@/components/ui/spinner"
import { useIsOwner } from "@/features/auth/queries"
import { buildFlow, FLOW_ORDER } from "../flows"
import { entryHref } from "../links"
import { useFlowContext } from "../queries"

export function EntryScreen() {
  const context = useFlowContext()
  const isOwner = useIsOwner()

  if (!context) return <Spinner className="mx-auto" />

  const flows = FLOW_ORDER.map((key) => buildFlow(key, context)).filter((flow) => isOwner || !flow.ownerOnly)

  return (
    <div className="flex flex-col gap-5">
      <PageHeader title="Que voulez-vous saisir ?" description="Choisissez, puis répondez aux questions une par une." />
      <div className="grid grid-cols-[repeat(auto-fill,minmax(min(100%,160px),1fr))] gap-3">
        {flows.map((flow) => (
          <Link
            key={flow.key}
            href={entryHref(flow.key)}
            className="flex min-h-34 flex-col items-start gap-2.5 rounded-xl border bg-card p-4.5 text-card-foreground transition-colors hover:border-ring"
          >
            <InitialsAvatar color={flow.color}>
              <Icon name={flow.icon} className="text-[19px]" />
            </InitialsAvatar>
            <span className="mt-auto text-nav font-bold">{flow.title}</span>
            <span className="text-caption text-muted-foreground">{flow.description}</span>
          </Link>
        ))}
      </div>
    </div>
  )
}
