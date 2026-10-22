// Field-app view models. Dates are ISO strings so the query cache survives
// the IndexedDB round-trip. Replace with generated @pig-farm/types (#0.5).
import type { Currency } from "./format";

export interface FarmSummary {
  id: string;
  name: string;
  currency: Currency;
  user: { firstName: string; initials: string; unreadNotifications: number };
  herd: { total: number; weeklyChange: number; adgGramsPerDay: number; mortalityPct: number; feedConversion: number };
}

export type TaskTone = "danger" | "warning" | "neutral";

export interface FarmTask {
  id: string;
  icon: string;
  title: string;
  detail: string;
  tone: TaskTone;
  href: string;
}

export interface WeighIn {
  date: string;
  averageKg: number;
}

export interface Lot {
  id: string;
  code: string;
  phase: string;
  location: string;
  startedOn: string;
  initialCount: number;
  currentCount: number;
  adgGramsPerDay: number;
  // Individual weights of the latest weigh-in session.
  weights: number[];
  // Lot average per weigh-in, oldest first.
  history: WeighIn[];
  photo?: string;
  vaccineDueToday: boolean;
  caregiver: { name: string; initials: string; shift: string; phone: string };
}

export interface CycleEvent {
  key: "service" | "ultrasound" | "maternity" | "farrowing";
  title: string;
  detail: string;
  date: string;
  done: boolean;
}

export interface Sow {
  id: string;
  code: string;
  breed: string;
  parity: number;
  status: "gestante" | "vide" | "allaitante";
  boar: string;
  serviceDate: string;
  cycle: CycleEvent[];
  bornAlivePerLitter: number;
  weaningSurvivalPct: number;
  photo?: string;
}

export interface Listing {
  lotId: string;
  category: string;
  title: string;
  available: number;
  averageKg: number;
  priceXofPerKg: number;
  marketXofPerKg: number;
  quantity: number;
  visible: boolean;
  published: boolean;
  photo?: string;
}
