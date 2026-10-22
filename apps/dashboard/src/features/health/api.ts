import { db, respond } from "@/mocks/db"
import type { Treatment } from "./types"

// TODO(#5.4): prophylaxis schedule endpoint.
const UPCOMING_TREATMENTS: Treatment[] = [
  {
    inDays: 1,
    product: "Vaccin PPC",
    target: "L-24-10",
    type: "Prophylaxie",
    dose: "2 ml/animal",
    caregiver: "Dr Ateba",
    urgency: "danger",
    tag: "Demain",
  },
  {
    inDays: 3,
    product: "Fer dextran",
    target: "Portée T-118",
    type: "Individuel",
    dose: "1 ml/porcelet",
    caregiver: "Jean Mvogo",
    urgency: "warning",
    tag: "Dans 3 j",
  },
  {
    inDays: 5,
    product: "Ivermectine",
    target: "L-24-08",
    type: "Lot",
    dose: "1 ml/33 kg",
    caregiver: "Jean Mvogo",
    urgency: "neutral",
    tag: "Planifié",
  },
  {
    inDays: 9,
    product: "Rappel parvovirose",
    target: "Truies T-133, T-097",
    type: "Prophylaxie",
    dose: "2 ml/truie",
    caregiver: "Dr Ateba",
    urgency: "neutral",
    tag: "Planifié",
  },
  {
    inDays: 14,
    product: "Vermifuge",
    target: "L-24-07",
    type: "Lot",
    dose: "Selon notice",
    caregiver: "Paul Essomba",
    urgency: "neutral",
    tag: "Planifié",
  },
]

// TODO(#5.1): GET /stock-items.
export const healthApi = {
  stock: () => respond(db.read().stock),
  treatments: () => UPCOMING_TREATMENTS,
}
