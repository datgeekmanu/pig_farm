"use client"

import { useRouter } from "next/navigation"
import type { ColumnDef } from "@tanstack/react-table"
import { DataTable } from "@/components/shared/data-table"
import { formatNumber } from "@/lib/format"
import { buildingOf } from "./growth"
import type { Lot } from "./types"

const numeric = { numeric: true }

const COLUMNS: ColumnDef<Lot>[] = [
  { accessorKey: "id", header: "Lot", cell: ({ row }) => <b>{row.original.id}</b> },
  {
    accessorKey: "pen",
    header: "Bâtiment / loge",
    enableSorting: false,
    cell: ({ row }) => `${buildingOf(row.original)} · ${row.original.pen}`,
  },
  { accessorKey: "phase", header: "Phase" },
  { accessorKey: "headcount", header: "Effectif", meta: numeric, cell: ({ getValue }) => formatNumber(getValue<number>()) },
  { accessorKey: "avgWeightKg", header: "Poids moyen", meta: numeric, cell: ({ getValue }) => `${formatNumber(getValue<number>(), 1)} kg` },
  {
    accessorKey: "dailyGain",
    header: "GMQ",
    meta: numeric,
    cell: ({ getValue }) => (getValue<number>() ? `${formatNumber(getValue<number>())} g/j` : "—"),
  },
  {
    accessorKey: "feedConversion",
    header: "IC",
    meta: numeric,
    enableSorting: false,
    cell: ({ getValue }) => (getValue<number>() ? formatNumber(getValue<number>(), 2) : "—"),
  },
  {
    accessorKey: "uniformityCv",
    header: "CV",
    meta: numeric,
    enableSorting: false,
    cell: ({ getValue }) => `${formatNumber(getValue<number>(), 1)} %`,
  },
]

export function LotsTable({ lots }: { lots: Lot[] }) {
  const router = useRouter()
  return <DataTable columns={COLUMNS} data={lots} onRowClick={(lot) => router.push(`/animals/${lot.id}`)} />
}
