import { mortalityFlow, newLotFlow, treatmentFlow, weighingFlow } from "./herd"
import { expenseFlow, saleFlow, stockInFlow } from "./money"
import { farrowingFlow, serviceFlow, ultrasoundFlow } from "./reproduction"
import type { Flow, FlowContext, FlowKey } from "../types"

const BUILDERS: Record<FlowKey, (context: FlowContext) => Flow> = {
  weighing: weighingFlow,
  mortality: mortalityFlow,
  treatment: treatmentFlow,
  service: serviceFlow,
  ultrasound: ultrasoundFlow,
  farrowing: farrowingFlow,
  sale: saleFlow,
  expense: expenseFlow,
  "stock-in": stockInFlow,
  "new-lot": newLotFlow,
}

/** Display order of the "Que voulez-vous saisir ?" tiles. */
export const FLOW_ORDER = Object.keys(BUILDERS) as FlowKey[]

// hasOwn, not `in`: URL segments like "constructor" must not match prototype keys.
export const isFlowKey = (value: string): value is FlowKey => Object.hasOwn(BUILDERS, value)

export const buildFlow = (key: FlowKey, context: FlowContext) => BUILDERS[key](context)
