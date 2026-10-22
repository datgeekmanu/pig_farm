// Demo data until the Laravel endpoints exist (EPIC 2, 3, 6, 10).
// Dates are relative to "now" so the demo never goes stale.
import { addDays, DEFAULT_GESTATION_DAYS } from "./herd";
import type { FarmSummary, FarmTask, Listing, Lot, Sow } from "./models";

const iso = (daysFromNow: number) => addDays(new Date(), daysFromNow).toISOString();

export const farmSummary: FarmSummary = {
  id: "farm-koubri",
  name: "Ferme de Koubri",
  currency: "XOF",
  user: { firstName: "Awa", initials: "AK", unreadNotifications: 3 },
  herd: { total: 1248, weeklyChange: 36, adgGramsPerDay: 742, mortalityPct: 1.8, feedConversion: 2.61 },
};

export const tasks: FarmTask[] = [
  {
    id: "t1",
    icon: "syringe",
    title: "Rappel vaccin — Lot L-24",
    detail: "Aujourd'hui · Mycoplasme",
    tone: "danger",
    href: "/lots/l-24",
  },
  {
    id: "t2",
    icon: "baby-carriage",
    title: "Mise bas prévue — Truie T-118",
    detail: "Dans 2 jours",
    tone: "warning",
    href: "/repro/t-118",
  },
  {
    id: "t3",
    icon: "wheat-awn",
    title: "Stock bas — Aliment croissance",
    detail: "180 kg restants · seuil 500 kg",
    tone: "neutral",
    href: "/",
  },
];

export const lots: Lot[] = [
  {
    id: "l-24",
    code: "L-24",
    phase: "Engraissement",
    location: "Bât. B, loge 3",
    startedOn: iso(-63),
    initialCount: 200,
    currentCount: 196,
    adgGramsPerDay: 812,
    weights: [62.4, 71.8, 66.0, 74.2, 59.9, 69.5, 72.7, 64.1, 70.3, 67.8, 73.6],
    history: [
      { date: iso(-63), averageKg: 24.6 },
      { date: iso(-49), averageKg: 34.1 },
      { date: iso(-35), averageKg: 44.9 },
      { date: iso(-21), averageKg: 56.0 },
      { date: iso(-7), averageKg: 64.2 },
      { date: iso(0), averageKg: 68.4 },
    ],
    vaccineDueToday: true,
    caregiver: { name: "Ibrahim Ouédraogo", initials: "IO", shift: "06:30 – 14:00", phone: "+22670000000" },
  },
  {
    id: "l-19",
    code: "L-19",
    phase: "Finition",
    location: "Bât. A, loge 1",
    startedOn: iso(-112),
    initialCount: 92,
    currentCount: 88,
    adgGramsPerDay: 768,
    weights: [108.2, 115.6, 112.0, 104.8, 119.3, 110.7, 113.5, 109.9, 116.2, 107.4, 111.8],
    history: [
      { date: iso(-112), averageKg: 26.0 },
      { date: iso(-84), averageKg: 48.3 },
      { date: iso(-56), averageKg: 70.5 },
      { date: iso(-28), averageKg: 92.1 },
      { date: iso(0), averageKg: 112.0 },
    ],
    vaccineDueToday: false,
    caregiver: { name: "Mariam Sawadogo", initials: "MS", shift: "14:00 – 21:30", phone: "+22670000001" },
  },
];

const serviceDate = addDays(new Date(), -112);

export const sows: Sow[] = [
  {
    id: "t-118",
    code: "T-118",
    breed: "Large White",
    parity: 4,
    status: "gestante",
    boar: "V-07",
    serviceDate: serviceDate.toISOString(),
    cycle: [
      { key: "service", title: "Saillie", detail: "Verrat V-07 · insémination", date: iso(-112), done: true },
      { key: "ultrasound", title: "Échographie", detail: "Positive · J+24", date: iso(-88), done: true },
      { key: "maternity", title: "Entrée maternité", detail: "Bât. M, case 6", date: iso(-7), done: true },
      {
        key: "farrowing",
        title: "Mise bas",
        detail: `J${DEFAULT_GESTATION_DAYS} · calcul configurable`,
        date: addDays(serviceDate, DEFAULT_GESTATION_DAYS).toISOString(),
        done: false,
      },
    ],
    bornAlivePerLitter: 12.3,
    weaningSurvivalPct: 91,
  },
];

export const listings: Listing[] = [
  {
    lotId: "l-19",
    category: "Porc charcutier",
    title: "Lot L-19 · Finition",
    available: 88,
    averageKg: 112,
    priceXofPerKg: 1700,
    marketXofPerKg: 1650,
    quantity: 10,
    visible: true,
    published: false,
  },
];
