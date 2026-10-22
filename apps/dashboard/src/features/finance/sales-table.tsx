"use client"

import type { ColumnDef } from "@tanstack/react-table"
import { Badge } from "@/components/ui/badge"
import { DataTable } from "@/components/shared/data-table"
import { useActiveFarm } from "@/features/farms/active-farm"
import { formatMoney, formatNumber } from "@/lib/format"
import type { Currency } from "@/features/farms/types"
import type { Sale } from "./types"

const columnsFor = (currency: Currency): ColumnDef<Sale>[] => [
  { accessorKey: "date", header: "Date", enableSorting: false },
  { accessorKey: "buyer", header: "Acheteur", enableSorting: false, cell: ({ row }) => <b>{row.original.buyer}</b> },
  { accessorKey: "items", header: "Articles", enableSorting: false },
  {
    accessorKey: "weightKg",
    header: "Poids total",
    enableSorting: false,
    meta: { numeric: true },
    cell: ({ getValue }) => `${formatNumber(getValue<number>())} kg`,
  },
  { accessorKey: "amount", header: "Montant", meta: { numeric: true }, cell: ({ getValue }) => formatMoney(getValue<number>(), currency) },
  {
    accessorKey: "status",
    header: "Paiement",
    enableSorting: false,
    cell: ({ row }) => <Badge tone={row.original.tone}>{row.original.status}</Badge>,
  },
]

export function SalesTable({ sales }: { sales: Sale[] }) {
  const { farm } = useActiveFarm()
  return <DataTable columns={columnsFor(farm.currency)} data={sales} />
}
