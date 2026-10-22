"use client"

import { useParams, useRouter, useSearchParams } from "next/navigation"
import { useState } from "react"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Spinner } from "@/components/ui/spinner"
import { EmptyState } from "@/components/shared/empty-state"
import { InitialsAvatar } from "@/components/shared/initials-avatar"
import { useIsOwner } from "@/features/auth/queries"
import { cn } from "@/lib/utils"
import { buildFlow, isFlowKey } from "../flows"
import { useFlowContext, useSubmitEntry } from "../queries"
import { resolveAnswers, validateField } from "../validation"
import type { Answers, Flow } from "../types"
import { RecapPanel } from "./recap-panel"
import { StepFields } from "./step-fields"

export function EntryWizard() {
  const { flow: flowKey } = useParams<{ flow: string }>()
  const context = useFlowContext()
  const isOwner = useIsOwner()

  if (!isFlowKey(flowKey)) return <EmptyState icon="circle-question">Cette saisie n’existe pas.</EmptyState>
  if (!context) return <Spinner className="mx-auto" />

  const flow = buildFlow(flowKey, context)
  if (flow.ownerOnly && !isOwner) return <EmptyState icon="lock">Cette saisie est réservée au propriétaire de la ferme.</EmptyState>

  return <Wizard flow={flow} />
}

function Wizard({ flow }: { flow: Flow }) {
  const router = useRouter()
  const searchParams = useSearchParams()
  const submit = useSubmitEntry()
  const taskId = searchParams.get("task") ?? undefined

  // Query params pre-fill answers, e.g. /entry/weighing?lot=L-24-07.
  const [answers, setAnswers] = useState<Answers>(() => Object.fromEntries(searchParams))
  const [step, setStep] = useState(0)
  const [tried, setTried] = useState(false)

  const isRecap = step === flow.steps.length
  const fields = isRecap ? [] : flow.steps[step].fields
  const totalSteps = flow.steps.length + 1

  const setAnswer = (key: string, value: string) => setAnswers((prev) => ({ ...prev, [key]: value }))

  const goBack = () => {
    if (step === 0) return router.back()
    setTried(false)
    setStep(step - 1)
  }

  const goNext = () => {
    if (isRecap) return submit.mutate({ flow: flow.key, answers, taskId })
    if (fields.some((field) => validateField(field, answers))) return setTried(true)
    // Freeze defaults (e.g. today's date) so the recap shows what will be saved.
    setAnswers((prev) => ({ ...prev, ...resolveAnswers(fields, prev) }))
    setTried(false)
    setStep(step + 1)
  }

  return (
    <div className="mx-auto flex w-full max-w-160 flex-col gap-5">
      <div className="flex items-center gap-3">
        <Button variant="outline" size="icon-lg" aria-label="Fermer la saisie" onClick={() => router.back()}>
          <Icon name="xmark" />
        </Button>
        <span className="flex flex-1 flex-col">
          <span className="text-caption text-muted-foreground">
            Étape {step + 1} sur {totalSteps}
          </span>
          <b className="font-heading text-base">{flow.title}</b>
        </span>
        <InitialsAvatar size="sm" color={flow.color}>
          <Icon name={flow.icon} />
        </InitialsAvatar>
      </div>

      <div className="flex gap-1.5" aria-hidden>
        {Array.from({ length: totalSteps }, (_, i) => (
          <span key={i} className={cn("h-1.5 flex-1 rounded-full", i <= step ? "bg-primary" : "bg-muted")} />
        ))}
      </div>

      <h1 className="text-2xl font-bold">{isRecap ? "Vérifiez avant d’enregistrer" : flow.steps[step].question}</h1>

      {isRecap ? (
        <RecapPanel recap={flow.recap(answers)} />
      ) : (
        <StepFields fields={fields} answers={answers} showErrors={tried} onAnswer={setAnswer} />
      )}

      {/* Sticky action bar keeps the main action under the thumb on mobile. */}
      <div className="sticky -bottom-10 z-10 flex gap-2.5 border-t bg-background pt-3 pb-10">
        <Button variant="outline" className="h-14 px-5 text-base" onClick={goBack}>
          <Icon name="arrow-left" />
          {step === 0 ? "Annuler" : "Retour"}
        </Button>
        <Button className="h-14 flex-1 text-base font-bold" loading={submit.isPending} onClick={goNext}>
          {isRecap ? flow.saveLabel : "Continuer"}
          {!submit.isPending && <Icon name={isRecap ? "check" : "arrow-right"} />}
        </Button>
      </div>
    </div>
  )
}
