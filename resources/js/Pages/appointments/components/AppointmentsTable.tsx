import * as React from "react";
import { Link } from "@inertiajs/react";
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from "@tanstack/react-table";
import { Eye, Pencil, RotateCcw, Trash2 } from "lucide-react";
import { DataTable } from "@/shadcn/data-table";
import { formatDateShort } from "@/utils/dateFormatter";
import type { AppointmentListItem } from "@/modules/appointments/types";

interface AppointmentsTableProps {
    data: AppointmentListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, fullName: string) => void;
    onRestoreClick: (uuid: string, fullName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<AppointmentListItem>();

export default function AppointmentsTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: AppointmentsTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: "select",
                header: ({ table }) => (
                    <input type="checkbox" checked={table.getIsAllPageRowsSelected()} onChange={table.getToggleAllPageRowsSelectedHandler()} aria-label="Select all" className="h-4 w-4 cursor-pointer rounded" style={{ accentColor: "var(--accent-primary)" }} />
                ),
                cell: ({ row }) => (
                    <input type="checkbox" checked={row.getIsSelected()} onChange={row.getToggleSelectedHandler()} aria-label="Select row" className="h-4 w-4 cursor-pointer rounded" style={{ accentColor: "var(--accent-primary)" }} />
                ),
            }),
            columnHelper.display({
                id: "full_name",
                header: "Appointment",
                cell: ({ row }) => (
                    <div className="flex flex-col">
                        <span className="font-semibold" style={{ color: "var(--text-primary)" }}>{row.original.full_name}</span>
                        <span className="text-xs" style={{ color: "var(--text-muted)" }}>{row.original.email ?? row.original.phone ?? "—"}</span>
                    </div>
                ),
            }),
            columnHelper.display({
                id: "inspection_status",
                header: "Inspection",
                cell: ({ row }) => (
                    <span className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase" style={{ color: "var(--accent-info)", background: "color-mix(in srgb, var(--accent-info) 15%, transparent)", border: "1px solid color-mix(in srgb, var(--accent-info) 25%, transparent)" }}>
                        {row.original.inspection_status}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "status_lead",
                header: "Lead",
                cell: ({ row }) => (
                    <span className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase" style={{ color: "var(--accent-warning)", background: "color-mix(in srgb, var(--accent-warning) 15%, transparent)", border: "1px solid color-mix(in srgb, var(--accent-warning) 25%, transparent)" }}>
                        {row.original.status_lead}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "inspection_date",
                header: "Inspection date",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-secondary)" }}>
                        {row.original.inspection_date ? formatDateShort(row.original.inspection_date) : "—"}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "created_at",
                header: "Created",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-muted)" }}>
                        {formatDateShort(row.original.created_at)}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "actions",
                header: "Actions",
                cell: ({ row }) => {
                    const appointment = row.original;
                    const isDeleted = Boolean(appointment.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link href={`/appointments/${appointment.uuid}`} className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="View appointment" aria-label="View appointment">
                                <Eye size={14} />
                            </Link>
                            {!isDeleted ? (
                                <>
                                    <Link href={`/appointments/${appointment.uuid}/edit`} className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="Edit appointment" aria-label="Edit appointment">
                                        <Pencil size={14} />
                                    </Link>
                                    <button type="button" onClick={() => onDeleteClick(appointment.uuid, appointment.full_name)} className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="Delete appointment" aria-label="Delete appointment" style={{ color: "var(--accent-error)", border: "1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))", background: "color-mix(in srgb, var(--accent-error) 10%, transparent)" }}>
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button type="button" onClick={() => onRestoreClick(appointment.uuid, appointment.full_name)} className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="Restore appointment" aria-label="Restore appointment" style={{ color: "var(--accent-success)", border: "1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))", background: "color-mix(in srgb, var(--accent-success) 10%, transparent)" }}>
                                    <RotateCcw size={14} />
                                </button>
                            )}
                        </div>
                    );
                },
            }),
        ],
        [onDeleteClick, onRestoreClick],
    );

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={false}
            noDataMessage="No appointments found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
