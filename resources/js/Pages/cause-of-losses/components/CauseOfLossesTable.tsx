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
import type { CauseOfLossListItem } from "@/modules/cause-of-losses/types";

interface CauseOfLossesTableProps {
    data: CauseOfLossListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, causeLossName: string) => void;
    onRestoreClick: (uuid: string, causeLossName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<CauseOfLossListItem>();

export default function CauseOfLossesTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: CauseOfLossesTableProps): React.JSX.Element {
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
                id: "cause_loss_name",
                header: "Cause of Loss",
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                        {row.original.cause_loss_name}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "description",
                header: "Description",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-secondary)" }}>
                        {row.original.description ?? "—"}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "severity",
                header: "Severity",
                cell: ({ row }) => {
                    const severity = row.original.severity;
                    const accent = severity === "high"
                        ? "var(--accent-error)"
                        : severity === "medium"
                            ? "var(--accent-warning)"
                            : "var(--accent-success)";

                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                            style={{
                                color: accent,
                                background: `color-mix(in srgb, ${accent} 15%, transparent)`,
                                border: `1px solid color-mix(in srgb, ${accent} 25%, transparent)`,
                            }}
                        >
                            {severity}
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
                    const causeOfLoss = info.row.original;
                    const isDeleted = Boolean(causeOfLoss.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/cause-of-losses/${causeOfLoss.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View cause of loss"
                                aria-label="View cause of loss"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/cause-of-losses/${causeOfLoss.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit cause of loss"
                                        aria-label="Edit cause of loss"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(causeOfLoss.uuid, causeOfLoss.cause_loss_name)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete cause of loss"
                                        aria-label="Delete cause of loss"
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
                                    onClick={() => onRestoreClick(causeOfLoss.uuid, causeOfLoss.cause_loss_name)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore cause of loss"
                                    aria-label="Restore cause of loss"
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
            noDataMessage="No cause of losses found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
