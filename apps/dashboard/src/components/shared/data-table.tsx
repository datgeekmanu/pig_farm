"use client"

import { useState } from "react"
import {
  flexRender,
  getCoreRowModel,
  getPaginationRowModel,
  getSortedRowModel,
  useReactTable,
  type ColumnDef,
  type SortingState,
} from "@tanstack/react-table"
import { Icon } from "@/components/icon"
import { Button } from "@/components/ui/button"
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from "@/components/ui/table"
import { formatNumber } from "@/lib/format"
import { cn } from "@/lib/utils"

/** Right-aligns a column and switches it to tabular figures. */
type ColumnMeta = { numeric?: boolean }

type DataTableProps<T> = {
  columns: ColumnDef<T>[]
  data: T[]
  onRowClick?: (row: T) => void
  emptyMessage?: string
  pageSize?: number
}

export function DataTable<T>({ columns, data, onRowClick, emptyMessage = "Aucun enregistrement", pageSize = 10 }: DataTableProps<T>) {
  const [sorting, setSorting] = useState<SortingState>([])
  // TanStack Table returns non-memoizable functions; the React Compiler knows to skip it.
  // eslint-disable-next-line react-hooks/incompatible-library
  const table = useReactTable({
    data,
    columns,
    state: { sorting },
    onSortingChange: setSorting,
    getCoreRowModel: getCoreRowModel(),
    getSortedRowModel: getSortedRowModel(),
    getPaginationRowModel: getPaginationRowModel(),
    initialState: { pagination: { pageSize } },
  })

  const rows = table.getRowModel().rows
  const { pageIndex } = table.getState().pagination
  const firstRow = pageIndex * pageSize + 1
  const lastRow = Math.min(data.length, firstRow + pageSize - 1)

  return (
    <div className="min-w-0 overflow-hidden rounded-lg border bg-card">
      <Table>
        <TableHeader>
          {table.getHeaderGroups().map((group) => (
            <TableRow key={group.id} className="hover:bg-transparent">
              {group.headers.map((header) => (
                <TableHead
                  key={header.id}
                  aria-sort={sortDirection(header.column.getIsSorted())}
                  className={cn(
                    "h-11 bg-muted px-4 text-caption font-semibold text-muted-foreground",
                    isNumeric(header.column.columnDef) && "text-right",
                  )}
                >
                  {header.column.getCanSort() ? (
                    <button
                      type="button"
                      onClick={header.column.getToggleSortingHandler()}
                      className="inline-flex cursor-pointer items-center gap-1.5"
                    >
                      {flexRender(header.column.columnDef.header, header.getContext())}
                      <SortIcon direction={header.column.getIsSorted()} />
                    </button>
                  ) : (
                    flexRender(header.column.columnDef.header, header.getContext())
                  )}
                </TableHead>
              ))}
            </TableRow>
          ))}
        </TableHeader>
        <TableBody>
          {rows.length === 0 ? (
            <TableRow>
              <TableCell colSpan={columns.length} className="py-12 text-center text-muted-foreground">
                <Icon name="inbox" className="mb-2.5 block text-[22px]" />
                {emptyMessage}
              </TableCell>
            </TableRow>
          ) : (
            rows.map((row) => (
              <TableRow
                key={row.id}
                onClick={onRowClick && (() => onRowClick(row.original))}
                className={cn("hover:bg-transparent", onRowClick && "cursor-pointer hover:bg-hover-overlay")}
              >
                {row.getVisibleCells().map((cell) => (
                  <TableCell
                    key={cell.id}
                    className={cn("px-4 py-3 text-sm", isNumeric(cell.column.columnDef) && "text-right whitespace-nowrap tabular-nums")}
                  >
                    {flexRender(cell.column.columnDef.cell, cell.getContext())}
                  </TableCell>
                ))}
              </TableRow>
            ))
          )}
        </TableBody>
      </Table>

      {table.getPageCount() > 1 && (
        <div className="flex items-center justify-between px-4 py-2.5 text-caption text-muted-foreground tabular-nums">
          <span>
            {firstRow}–{lastRow} sur {formatNumber(data.length)} lignes
          </span>
          <div className="flex gap-1.5">
            <Button
              variant="outline"
              size="icon-sm"
              aria-label="Page précédente"
              disabled={!table.getCanPreviousPage()}
              onClick={() => table.previousPage()}
            >
              <Icon name="chevron-left" />
            </Button>
            <Button
              variant="outline"
              size="icon-sm"
              aria-label="Page suivante"
              disabled={!table.getCanNextPage()}
              onClick={() => table.nextPage()}
            >
              <Icon name="chevron-right" />
            </Button>
          </div>
        </div>
      )}
    </div>
  )
}

type SortDirection = false | "asc" | "desc"

function SortIcon({ direction }: { direction: SortDirection }) {
  const name = direction === "asc" ? "sort-up" : direction === "desc" ? "sort-down" : "sort"
  return <Icon name={name} className={cn("text-[11px]", !direction && "opacity-45")} />
}

const sortDirection = (direction: SortDirection) => (direction === "asc" ? "ascending" : direction === "desc" ? "descending" : undefined)

const isNumeric = <T,>(column: ColumnDef<T>) => (column.meta as ColumnMeta | undefined)?.numeric
