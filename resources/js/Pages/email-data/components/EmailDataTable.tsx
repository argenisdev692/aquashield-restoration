import * as React from "react";
import { Link } from "@inertiajs/react";
import { PermissionGuard } from "@/modules/auth/components/PermissionGuard";
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from "@tanstack/react-table";
import { Eye, Pencil, RotateCcw, Trash2 } from "lucide-react";
import { useAuthContext } from "@/modules/auth/context/AuthContext";
import { DataTable } from "@/shadcn/data-table";
import { formatDateShort } from "@/utils/dateFormatter";
import type { EmailDataListItem } from "@/modules/email-data/types";

interface EmailDataTableProps {
    data: EmailDataListItem[];
    isPending: boolean;
    isError: boolean;
    onDeleteClick: (uuid: string, email: string) => void;
    onRestoreClick: (uuid: string, email: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<EmailDataListItem>();

export default function EmailDataTable({
    data,
    isPending,
    isError,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: EmailDataTableProps): React.JSX.Element {
    const { permissions, isSuperAdmin } = useAuthContext();
    const canDelete = isSuperAdmin || permissions.includes("DELETE_EMAIL_DATA");
    const canUpdate = isSuperAdmin || permissions.includes("UPDATE_EMAIL_DATA");
    const canRestore = isSuperAdmin || permissions.includes("RESTORE_EMAIL_DATA");

    const columns = React.useMemo(() => {
        const baseColumns = [
            columnHelper.display({
                id: "email",
                header: "Email",
                cell: ({ row }) => (
                    <div className="flex flex-col text-left">
                        <span className="font-semibold" style={{ color: "var(--text-primary)" }}>
                            {row.original.email}
                        </span>
                        <span className="text-xs" style={{ color: "var(--text-muted)" }}>
                            Owner #{row.original.user_id}
                        </span>
                    </div>
                ),
            }),
            columnHelper.display({
                id: "type",
                header: "Type",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-secondary)" }}>
                        {row.original.type ?? "—"}
                    </span>
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
                id: "description",
                header: "Description",
                cell: ({ row }) => (
                    <span style={{ color: "var(--text-secondary)" }}>
                        {row.original.description ?? "—"}
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
                    const emailData = row.original;
                    const isDeleted = Boolean(emailData.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/email-data/${emailData.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View email data"
                                aria-label="View email data"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted && canUpdate ? (
                                <PermissionGuard permissions={["UPDATE_EMAIL_DATA"]}>
                                    <Link
                                        href={`/email-data/${emailData.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit email data"
                                        aria-label="Edit email data"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                </PermissionGuard>
                            ) : null}

                            {!isDeleted && canDelete ? (
                                <PermissionGuard permissions={["DELETE_EMAIL_DATA"]}>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(emailData.uuid, emailData.email)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete email data"
                                        aria-label="Delete email data"
                                        style={{
                                            color: "var(--accent-error)",
                                            border: "1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))",
                                            background: "color-mix(in srgb, var(--accent-error) 10%, transparent)",
                                        }}
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </PermissionGuard>
                            ) : null}

                            {isDeleted && canRestore ? (
                                <PermissionGuard permissions={["RESTORE_EMAIL_DATA"]}>
                                    <button
                                        type="button"
                                        onClick={() => onRestoreClick(emailData.uuid, emailData.email)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Restore email data"
                                        aria-label="Restore email data"
                                        style={{
                                            color: "var(--accent-success)",
                                            border: "1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))",
                                            background: "color-mix(in srgb, var(--accent-success) 10%, transparent)",
                                        }}
                                    >
                                        <RotateCcw size={14} />
                                    </button>
                                </PermissionGuard>
                            ) : null}
                        </div>
                    );
                },
            }),
        ];

        if (!canDelete) {
            return baseColumns;
        }

        return [
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
            ...baseColumns,
        ];
    }, [canDelete, canRestore, canUpdate, onDeleteClick, onRestoreClick]);

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={isError}
            noDataMessage="No email data records found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
