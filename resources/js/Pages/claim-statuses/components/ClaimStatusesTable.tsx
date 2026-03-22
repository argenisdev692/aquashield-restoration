import * as React from "react";
import { Link } from "@inertiajs/react";
import {
    createColumnHelper,
    type ColumnDef,
    type OnChangeFn,
    type RowSelectionState,
} from "@tanstack/react-table";
import { Eye, Pencil, RotateCcw, Trash2 } from "lucide-react";
import { DataTable } from "@/shadcn/data-table";
import type { ClaimStatusListItem } from "@/modules/claim-statuses/types";

const columnHelper = createColumnHelper<ClaimStatusListItem>();

interface ClaimStatusesTableProps {
    data: ClaimStatusListItem[];
    isPending: boolean;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
    onDeleteClick: (uuid: string, name: string) => void;
    onRestoreClick: (uuid: string, name: string) => void;
}

export default function ClaimStatusesTable({
    data,
    isPending,
    rowSelection,
    onRowSelectionChange,
    onDeleteClick,
    onRestoreClick,
}: ClaimStatusesTableProps): React.JSX.Element {
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
                        className="h-4 w-4 rounded"
                        style={{ accentColor: "var(--accent-primary)" }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label="Select row"
                        className="h-4 w-4 rounded"
                        style={{ accentColor: "var(--accent-primary)" }}
                    />
                ),
                size: 40,
            }),
            columnHelper.accessor("claim_status_name", {
                header: "Status Name",
                cell: ({ row, getValue }) => (
                    <Link
                        href={`/claim-statuses/${row.original.uuid}`}
                        className="font-semibold text-sm transition-colors"
                        style={{ color: "var(--accent-primary)" }}
                    >
                        {getValue()}
                    </Link>
                ),
            }),
            columnHelper.accessor("background_color", {
                header: "Color",
                cell: ({ getValue }) => {
                    const color = getValue();
                    return color ? (
                        <div className="flex items-center gap-2">
                            <span
                                className="inline-block h-5 w-5 rounded-md border"
                                style={{
                                    backgroundColor: color,
                                    borderColor: "var(--border-default)",
                                }}
                                aria-label={`Color swatch ${color}`}
                            />
                            <span
                                className="text-xs font-mono"
                                style={{ color: "var(--text-secondary)" }}
                            >
                                {color}
                            </span>
                        </div>
                    ) : (
                        <span style={{ color: "var(--text-disabled)" }}>—</span>
                    );
                },
            }),
            columnHelper.accessor("created_at", {
                header: "Created",
                cell: ({ getValue }) => (
                    <span
                        className="text-sm"
                        style={{ color: "var(--text-secondary)" }}
                    >
                        {new Date(getValue()).toLocaleDateString()}
                    </span>
                ),
            }),
            columnHelper.accessor("deleted_at", {
                header: "Status",
                cell: ({ getValue }) => {
                    const deletedAt = getValue();
                    return (
                        <span
                            className="inline-flex items-center rounded-full px-2.5 py-0.5 text-xs font-semibold"
                            style={{
                                background: deletedAt
                                    ? "color-mix(in srgb, var(--accent-error) 15%, transparent)"
                                    : "color-mix(in srgb, var(--accent-success) 15%, transparent)",
                                color: deletedAt
                                    ? "var(--accent-error)"
                                    : "var(--accent-success)",
                            }}
                        >
                            {deletedAt ? "Deleted" : "Active"}
                        </span>
                    );
                },
            }),
            columnHelper.display({
                id: "actions",
                header: "Actions",
                cell: ({ row }) => {
                    const claimStatus = row.original;
                    const isDeleted = claimStatus.deleted_at !== null;

                    return (
                        <div className="flex items-center gap-1.5">
                            <Link
                                href={`/claim-statuses/${claimStatus.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View claim status"
                                aria-label="View claim status"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/claim-statuses/${claimStatus.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit claim status"
                                        aria-label="Edit claim status"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onDeleteClick(
                                                claimStatus.uuid,
                                                claimStatus.claim_status_name,
                                            )
                                        }
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete claim status"
                                        aria-label="Delete claim status"
                                        style={{
                                            color: "var(--accent-error)",
                                            border: "1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))",
                                            background:
                                                "color-mix(in srgb, var(--accent-error) 10%, transparent)",
                                        }}
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() =>
                                        onRestoreClick(
                                            claimStatus.uuid,
                                            claimStatus.claim_status_name,
                                        )
                                    }
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore claim status"
                                    aria-label="Restore claim status"
                                    style={{
                                        color: "var(--accent-success)",
                                        border: "1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))",
                                        background:
                                            "color-mix(in srgb, var(--accent-success) 10%, transparent)",
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
    ) as ColumnDef<ClaimStatusListItem>[];

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={false}
            noDataMessage="No claim statuses found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
