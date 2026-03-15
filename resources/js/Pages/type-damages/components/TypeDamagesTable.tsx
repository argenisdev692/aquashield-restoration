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
import type { TypeDamageListItem } from "@/modules/type-damages/types";

interface TypeDamagesTableProps {
    data: TypeDamageListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, typeDamageName: string) => void;
    onRestoreClick: (uuid: string, typeDamageName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<TypeDamageListItem>();

export default function TypeDamagesTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: TypeDamagesTableProps): React.JSX.Element {
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
                id: "type_damage_name",
                header: "Type Damage",
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                        {row.original.type_damage_name}
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
                    const typeDamage = info.row.original;
                    const isDeleted = Boolean(typeDamage.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/type-damages/${typeDamage.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View type damage"
                                aria-label="View type damage"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/type-damages/${typeDamage.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit type damage"
                                        aria-label="Edit type damage"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(typeDamage.uuid, typeDamage.type_damage_name)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete type damage"
                                        aria-label="Delete type damage"
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
                                    onClick={() => onRestoreClick(typeDamage.uuid, typeDamage.type_damage_name)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore type damage"
                                    aria-label="Restore type damage"
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
            noDataMessage="No type damages found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
