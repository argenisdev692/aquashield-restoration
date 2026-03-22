import * as React from 'react';
import { Link } from '@inertiajs/react';
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from '@tanstack/react-table';
import { Eye, Image, Pencil, RotateCcw, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { PortfolioListItem } from '@/modules/portfolios/types';

interface PortfoliosTableProps {
    data: PortfolioListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, label: string) => void;
    onRestoreClick: (uuid: string, label: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<PortfolioListItem>();

export default function PortfoliosTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: PortfoliosTableProps): React.JSX.Element {
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
                id: 'thumbnail',
                header: 'Preview',
                cell: ({ row }) => {
                    const path = row.original.first_image_path;
                    return path !== null && path !== undefined ? (
                        <div
                            className="h-12 w-16 overflow-hidden rounded-lg"
                            style={{ border: '1px solid var(--border-default)' }}
                        >
                            <img
                                src={`/storage/${path}`}
                                alt="Portfolio preview"
                                className="h-full w-full object-cover"
                            />
                        </div>
                    ) : (
                        <div
                            className="flex h-12 w-16 items-center justify-center rounded-lg"
                            style={{
                                border: '1px solid var(--border-default)',
                                background: 'var(--bg-subtle)',
                                color: 'var(--text-muted)',
                            }}
                        >
                            <Image size={16} />
                        </div>
                    );
                },
            }),
            columnHelper.display({
                id: 'project_type',
                header: 'Project Type',
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                        {row.original.project_type_title ?? '—'}
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
                id: 'image_count',
                header: 'Images',
                cell: ({ row }) => (
                    <span
                        className="inline-flex items-center gap-1 text-sm"
                        style={{ color: 'var(--text-muted)' }}
                    >
                        <Image size={13} />
                        {row.original.image_count}
                    </span>
                ),
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
                    const label = item.project_type_title ?? item.uuid.slice(0, 8);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/portfolios/${item.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View portfolio"
                                aria-label="View portfolio"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/portfolios/${item.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit portfolio"
                                        aria-label="Edit portfolio"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(item.uuid, label)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete portfolio"
                                        aria-label="Delete portfolio"
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
                                    onClick={() => onRestoreClick(item.uuid, label)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore portfolio"
                                    aria-label="Restore portfolio"
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
            noDataMessage="No portfolios found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
