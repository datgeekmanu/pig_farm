import { currencyLabel, formatMoney, formatNumber, toXof } from "@/lib/format"
import { chips, dateField, findLot, headcountField, longDate, lotStep, num } from "./shared"
import type { Flow, FlowContext } from "../types"

export function saleFlow(context: FlowContext): Flow {
  const currency = currencyLabel(context.currency)
  return {
    key: "sale",
    title: "Vente",
    description: "Animaux vendus",
    icon: "sack-dollar",
    color: "forest",
    saveLabel: "Enregistrer la vente",
    ownerOnly: true,
    steps: [
      lotStep(context, "De quel lot viennent les animaux ?"),
      {
        question: "Combien et à quel prix ?",
        fields: [
          headcountField(context, "Animaux vendus", () => "10"),
          { key: "totalKg", type: "number", label: "Poids total", unit: () => "kg", placeholder: "ex. 1 120" },
          {
            key: "pricePerKg",
            type: "number",
            label: "Prix au kilo",
            unit: () => `${currency}/kg`,
            placeholder: context.currency === "EUR" ? "ex. 2,6" : "ex. 1 700",
          },
        ],
      },
      {
        question: "Qui achète, et comment paie-t-il ?",
        fields: [
          { key: "buyer", type: "text", label: "Acheteur", placeholder: "ex. Boucherie Mfoundi" },
          { key: "payment", type: "chips", label: "Paiement", options: chips(["Espèces", "Mobile money", "Virement"]) },
          { key: "status", type: "chips", label: "Statut", options: chips(["Payé", "Partiel", "En attente"]) },
          dateField("Date"),
        ],
      },
    ],
    recap: (answers) => {
      const lot = findLot(context, answers)!
      const total = toXof(num(answers, "totalKg") * num(answers, "pricePerKg"), context.currency)
      return {
        rows: [
          { label: "Lot", value: lot.id },
          { label: "Animaux", value: formatNumber(num(answers, "count")) },
          { label: "Poids total", value: `${formatNumber(num(answers, "totalKg"))} kg` },
          { label: "Prix", value: `${formatNumber(num(answers, "pricePerKg"), context.currency === "EUR" ? 2 : 0)} ${currency}/kg` },
          { label: "Montant total", value: formatMoney(total, context.currency), emphasis: true },
          { label: "Acheteur", value: answers.buyer },
          { label: "Paiement", value: `${answers.payment} · ${answers.status}` },
        ],
        note: `${formatNumber(num(answers, "count"))} animaux seront retirés du lot ${lot.id}. Un reçu PDF sera disponible.`,
      }
    },
  }
}

export function expenseFlow(context: FlowContext): Flow {
  return {
    key: "expense",
    title: "Dépense",
    description: "Achat, facture",
    icon: "receipt",
    color: "petrol",
    saveLabel: "Enregistrer la dépense",
    ownerOnly: true,
    steps: [
      {
        question: "Quelle dépense ?",
        fields: [
          {
            key: "category",
            type: "chips",
            label: "Catégorie",
            options: chips(["Alimentation", "Vétérinaire", "Main d’œuvre", "Énergie", "Transport", "Équipement", "Autre"]),
          },
          { key: "amount", type: "number", label: "Montant", unit: () => currencyLabel(context.currency), placeholder: "ex. 85 000" },
          { key: "description", type: "text", label: "Description", placeholder: "ex. Gasoil groupe électrogène", optional: true },
          dateField("Date"),
        ],
      },
    ],
    recap: (answers) => ({
      rows: [
        { label: "Catégorie", value: answers.category },
        { label: "Montant", value: formatMoney(toXof(num(answers, "amount"), context.currency), context.currency), emphasis: true },
        { label: "Description", value: answers.description || "—" },
        { label: "Date", value: longDate(answers.date) },
      ],
    }),
  }
}

export function stockInFlow(context: FlowContext): Flow {
  const itemOf = (key: string) => context.stock.find((item) => item.key === key)
  return {
    key: "stock-in",
    title: "Entrée de stock",
    description: "Livraison reçue",
    icon: "box",
    color: "forest",
    saveLabel: "Enregistrer l’entrée",
    steps: [
      {
        question: "Quel article est arrivé ?",
        fields: [
          {
            key: "item",
            type: "choice",
            options: context.stock.map((item) => ({
              value: item.key,
              label: item.name,
              sub: `${item.location} · reste ${formatNumber(item.quantity)} ${item.unit}`,
              color: item.category === "feed" ? "earth" : "teal",
            })),
          },
        ],
      },
      {
        question: "Quelle quantité ?",
        fields: [
          {
            key: "quantity",
            type: "number",
            label: "Quantité reçue",
            unit: (answers) => itemOf(answers.item)?.unit,
            placeholder: "ex. 2 000",
          },
          dateField("Date de réception"),
        ],
      },
    ],
    recap: (answers) => {
      const item = itemOf(answers.item)!
      const received = num(answers, "quantity")
      return {
        rows: [
          { label: "Article", value: item.name },
          { label: "Quantité", value: `${formatNumber(received)} ${item.unit}`, emphasis: true },
          { label: "Coût estimé", value: formatMoney(received * item.unitPrice, context.currency) },
          { label: "Date", value: longDate(answers.date) },
        ],
        note: `Le stock passera de ${formatNumber(item.quantity)} à ${formatNumber(item.quantity + received)} ${item.unit}.`,
      }
    },
  }
}
