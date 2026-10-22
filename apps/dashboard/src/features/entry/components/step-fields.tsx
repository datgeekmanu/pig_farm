import { Icon } from "@/components/icon"
import { fieldValue, validateField } from "../validation"
import type { Answers, Field } from "../types"
import { FieldControl } from "./fields/field-control"

type StepFieldsProps = {
  fields: Field[]
  answers: Answers
  /** Errors only show after the user tried to continue. */
  showErrors: boolean
  onAnswer: (key: string, value: string) => void
}

export function StepFields({ fields, answers, showErrors, onAnswer }: StepFieldsProps) {
  return fields.map((field) => {
    const error = showErrors ? validateField(field, answers) : null
    return (
      <div key={field.key} className="flex flex-col gap-2.5">
        {field.label && (
          <span className="text-body font-semibold">
            {field.label}
            {field.optional && <span className="font-normal text-muted-foreground"> · facultatif</span>}
          </span>
        )}
        <FieldControl
          field={field}
          value={fieldValue(field, answers)}
          answers={answers}
          invalid={!!error}
          onChange={(value) => onAnswer(field.key, value)}
        />
        {error && (
          <span role="alert" className="flex items-center gap-2 text-sm font-semibold text-danger">
            <Icon name="circle-exclamation" />
            {error}
          </span>
        )}
      </div>
    )
  })
}
