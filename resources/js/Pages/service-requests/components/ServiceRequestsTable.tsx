import * as React from "react";
import { Link } from "@inertiajs/react";
import { createColumnHelper, type OnChangeFn, type RowSelectionState } from "@tanstack/react-table";
import { Eye, Pencil, RotateCcw, Trash2 } from "lucide-react";
import { DataTable } from "@/shadcn/data-table";
import { formatDateShort } from "@/utils/dateFormatter";
import type { ServiceRequestListItem } from "@/modules/service-requests/types";

interface ServiceRequestsTableProps {
    data: ServiceRequestListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, requestedService: string) => void;
    onRestoreClick: (uuid: string, requestedService: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<ServiceRequestListItem>();

export default function ServiceRequestsTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: ServiceRequestsTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: "select",
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: "var(--accent-primary)" }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label="Select row"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: "var(--accent-primary)" }}
                    />
                ),
            }),
            columnHelper.display({
                id: "requested_service",
                header: "Requested service",
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                        {row.original.requested_service}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "status",
                header: "Status",
                cell: ({ row }) => {
                    const isDeleted = Boolean(row.original.deleted_at);

                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                            style={{
                                color: isDeleted ? "var(--accent-error)" : "var(--accent-success)",
                                background: isDeleted
                                    ? "color-mix(in srgb, var(--accent-error) 15%, transparent)"
                                    : "color-mix(in srgb, var(--accent-success) 15%, transparent)",
                                border: isDeleted
                                    ? "1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)"
                                    : "1px solid color-mix(in srgb, var(--accent-success) 25%, transparent)",
                            }}
                        >
                            {isDeleted ? "Deleted" : "Active"}
                        </span>
                    );
                },
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
                cell: (info) => {
                    const serviceRequest = info.row.original;
                    const isDeleted = Boolean(serviceRequest.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/service-requests/${serviceRequest.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View service request"
                                aria-label="View service request"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/service-requests/${serviceRequest.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit service request"
                                        aria-label="Edit service request"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(serviceRequest.uuid, serviceRequest.requested_service)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete service request"
                                        aria-label="Delete service request"
                                        style={{
                                            color: "var(--accent-error)",
                                            border: "1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))",
                                            background: "color-mix(in srgb, var(--accent-error) 10%, transparent)",
                                        }}
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => onRestoreClick(serviceRequest.uuid, serviceRequest.requested_service)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore service request"
                                    aria-label="Restore service request"
                                    style={{
                                        color: "var(--accent-success)",
                                        border: "1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))",
                                        background: "color-mix(in srgb, var(--accent-success) 10%, transparent)",
                                    }}
                                >
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
            noDataMessage="No service requests found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
