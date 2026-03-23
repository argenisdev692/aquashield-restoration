import * as React from 'react';
import { Link } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { createColumnHelper } from '@tanstack/react-table';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import type { DocumentTemplateAdjuster } from '@/modules/document-template-adjusters/types';

interface DocumentTemplateAdjustersTableProps {
    data: DocumentTemplateAdjuster[];
    isPending: boolean;
    onDeleteClick: (uuid: string, label: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: React.Dispatch<React.SetStateAction<RowSelectionState>>;
}

const columnHelper = createColumnHelper<DocumentTemplateAdjuster>();

export default function DocumentTemplateAdjustersTable({
    data,
    isPending,
    onDeleteClick,
    rowSelection,
    onRowSelectionChange,
}: DocumentTemplateAdjustersTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: 'select',
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        ref={(el) => {
                            if (el) el.indeterminate = table.getIsSomePageRowsSelected();
                        }}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all rows"
                        style={{ accentColor: 'var(--accent-primary)', cursor: 'pointer' }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label="Select row"
                        style={{ accentColor: 'var(--accent-primary)', cursor: 'pointer' }}
                    />
                ),
            }),
            columnHelper.display({
                id: 'template_type_adjuster',
                header: 'Type',
                cell: ({ row }) => (
                    <span
                        className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                        style={{
                            color: 'var(--accent-primary)',
                            background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                            border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                        }}
                    >
                        {row.original.template_type_adjuster}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'template_description_adjuster',
                header: 'Description',
                cell: ({ row }) => (
                    <span
                        className="line-clamp-2 max-w-xs text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                    >
                        {row.original.template_description_adjuster ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'public_adjuster_name',
                header: 'Public Adjuster',
                cell: ({ row }) => (
                    <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>
                        {row.original.public_adjuster_name ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'uploaded_by_name',
                header: 'Uploaded By',
                cell: ({ row }) => (
                    <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
                        {row.original.uploaded_by_name ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'created_at',
                header: 'Created',
                cell: ({ row }) => (
                    <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
                        {formatDateShort(row.original.created_at)}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'actions',
                header: () => (
                    <span className="sr-only">Actions</span>
                ),
                cell: ({ row }) => {
                    const item = row.original;
                    const label = item.template_description_adjuster ?? item.template_type_adjuster;
                    return (
                        <div className="flex items-center justify-center gap-1">
                            <Link
                                href={`/document-template-adjusters/${item.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View template adjuster"
                                aria-label="View template adjuster"
                            >
                                <Eye size={14} />
                            </Link>
                            <PermissionGuard permissions={['UPDATE_DOCUMENT_TEMPLATE_ADJUSTER']}>
                                <Link
                                    href={`/document-template-adjusters/${item.uuid}/edit`}
                                    className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Edit template adjuster"
                                    aria-label="Edit template adjuster"
                                >
                                    <Pencil size={14} />
                                </Link>
                            </PermissionGuard>
                            <PermissionGuard permissions={['DELETE_DOCUMENT_TEMPLATE_ADJUSTER']}>
                                <button
                                    type="button"
                                    onClick={() => onDeleteClick(item.uuid, label)}
                                    className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Delete template adjuster"
                                    aria-label="Delete template adjuster"
                                    style={{ color: 'var(--accent-error)' }}
                                >
                                    <Trash2 size={14} />
                                </button>
                            </PermissionGuard>
                        </div>
                    );
                },
            }),
        ],
        [onDeleteClick],
    );

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isPending}
            isError={false}
            noDataMessage="No document template adjusters found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
