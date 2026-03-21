import * as React from 'react';
import { Link } from '@inertiajs/react';
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from '@tanstack/react-table';
import { Eye, Pencil, RotateCcw, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { ProjectTypeListItem } from '@/modules/project-types/types';

interface ProjectTypesTableProps {
    data: ProjectTypeListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, title: string) => void;
    onRestoreClick: (uuid: string, title: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<ProjectTypeListItem>();

export default function ProjectTypesTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: ProjectTypesTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: 'select',
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label="Select row"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
            }),
            columnHelper.display({
                id: 'title',
                header: 'Title',
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                        {row.original.title}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'service_category',
                header: 'Service Category',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-secondary)' }}>
                        {row.original.service_category_name ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'item_status',
                header: 'Item Status',
                cell: ({ row }) => {
                    const accent =
                        row.original.status === 'active'
                            ? 'var(--accent-success)'
                            : 'var(--accent-warning)';

                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                            style={{
                                color: accent,
                                background: `color-mix(in srgb, ${accent} 15%, transparent)`,
                                border: `1px solid color-mix(in srgb, ${accent} 25%, transparent)`,
                            }}
                        >
                            {row.original.status}
                        </span>
                    );
                },
            }),
            columnHelper.display({
                id: 'deleted_status',
                header: 'Record',
                cell: ({ row }) => {
                    const isDeleted = Boolean(row.original.deleted_at);
                    const accent = isDeleted ? 'var(--accent-error)' : 'var(--accent-success)';

                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold uppercase"
                            style={{
                                color: accent,
                                background: `color-mix(in srgb, ${accent} 15%, transparent)`,
                                border: `1px solid color-mix(in srgb, ${accent} 25%, transparent)`,
                            }}
                        >
                            {isDeleted ? 'Deleted' : 'Active'}
                        </span>
                    );
                },
            }),
            columnHelper.display({
                id: 'created_at',
                header: 'Created',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-muted)' }}>
                        {formatDateShort(row.original.created_at)}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'actions',
                header: 'Actions',
                cell: (info) => {
                    const item = info.row.original;
                    const isDeleted = Boolean(item.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/project-types/${item.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View project type"
                                aria-label="View project type"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/project-types/${item.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit project type"
                                        aria-label="Edit project type"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(item.uuid, item.title)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete project type"
                                        aria-label="Delete project type"
                                        style={{
                                            color: 'var(--accent-error)',
                                            border: '1px solid color-mix(in srgb, var(--accent-error) 30%, var(--border-default))',
                                            background: 'color-mix(in srgb, var(--accent-error) 10%, transparent)',
                                        }}
                                    >
                                        <Trash2 size={14} />
                                    </button>
                                </>
                            ) : (
                                <button
                                    type="button"
                                    onClick={() => onRestoreClick(item.uuid, item.title)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore project type"
                                    aria-label="Restore project type"
                                    style={{
                                        color: 'var(--accent-success)',
                                        border: '1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))',
                                        background: 'color-mix(in srgb, var(--accent-success) 10%, transparent)',
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
            noDataMessage="No project types found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
