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
import type { ContactSupportListItem } from "@/modules/contact-supports/types";

interface ContactSupportsTableProps {
    data: ContactSupportListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, fullName: string) => void;
    onRestoreClick: (uuid: string, fullName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<ContactSupportListItem>();

export default function ContactSupportsTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: ContactSupportsTableProps): React.JSX.Element {
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
                id: "full_name",
                header: "Contact",
                cell: ({ row }) => (
                    <div className="flex flex-col">
                        <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                            {row.original.full_name}
                        </span>
                        <span className="text-xs" style={{ color: "var(--text-muted)" }}>
                            {row.original.email}
                        </span>
                    </div>
                ),
            }),
            columnHelper.display({
                id: "phone",
                header: "Phone",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-secondary)" }}>
                        {row.original.phone ?? "—"}
                    </span>
                ),
            }),
            columnHelper.display({
                id: "readed",
                header: "Read status",
                cell: ({ row }) => {
                    const accent = row.original.readed ? "var(--accent-success)" : "var(--accent-warning)";

                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                            style={{
                                color: accent,
                                background: `color-mix(in srgb, ${accent} 15%, transparent)`,
                                border: `1px solid color-mix(in srgb, ${accent} 25%, transparent)`,
                            }}
                        >
                            {row.original.readed ? "Read" : "Unread"}
                        </span>
                    );
                },
            }),
            columnHelper.display({
                id: "sms_consent",
                header: "SMS",
                cell: ({ row }) => (
                    <span style={{ color: row.original.sms_consent ? "var(--accent-success)" : "var(--text-muted)" }}>
                        {row.original.sms_consent ? "Granted" : "No"}
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
                    const contactSupport = row.original;
                    const isDeleted = Boolean(contactSupport.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link href={`/contact-supports/${contactSupport.uuid}`} className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="View contact support" aria-label="View contact support">
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link href={`/contact-supports/${contactSupport.uuid}/edit`} className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="Edit contact support" aria-label="Edit contact support">
                                        <Pencil size={14} />
                                    </Link>
                                    <button type="button" onClick={() => onDeleteClick(contactSupport.uuid, contactSupport.full_name)} className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="Delete contact support" aria-label="Delete contact support" style={{ color: "var(--accent-error)", border: "1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))", background: "color-mix(in srgb, var(--accent-error) 10%, transparent)" }}>
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button type="button" onClick={() => onRestoreClick(contactSupport.uuid, contactSupport.full_name)} className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0" title="Restore contact support" aria-label="Restore contact support" style={{ color: "var(--accent-success)", border: "1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))", background: "color-mix(in srgb, var(--accent-success) 10%, transparent)" }}>
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
            noDataMessage="No contact support records found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
