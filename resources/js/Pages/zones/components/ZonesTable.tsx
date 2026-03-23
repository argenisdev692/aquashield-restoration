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
import type { ZoneListItem, ZoneType } from '@/modules/zones/types';
import { ZONE_TYPE_LABELS } from '@/modules/zones/types';

interface ZonesTableProps {
    data: ZoneListItem[];
    isPending: boolean;
    onDeleteClick: (uuid: string, zoneName: string) => void;
    onRestoreClick: (uuid: string, zoneName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const ZONE_TYPE_ACCENT: Record<ZoneType, string> = {
    interior:   'var(--accent-info)',
    exterior:   'var(--accent-success)',
    basement:   'var(--accent-warning)',
    attic:      'var(--accent-secondary)',
    garage:     'var(--accent-primary)',
    crawlspace: 'var(--accent-error)',
};

const columnHelper = createColumnHelper<ZoneListItem>();

export default function ZonesTable({
    data,
    isPending,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: ZonesTableProps): React.JSX.Element {
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
                id: 'zone_name',
                header: 'Zone Name',
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                        {row.original.zone_name}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'zone_type',
                header: 'Type',
                cell: ({ row }) => {
                    const type = row.original.zone_type;
                    const accent = ZONE_TYPE_ACCENT[type];
                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold capitalize"
                            style={{
                                color: accent,
                                background: `color-mix(in srgb, ${accent} 15%, transparent)`,
                                border: `1px solid color-mix(in srgb, ${accent} 25%, transparent)`,
                            }}
                        >
                            {ZONE_TYPE_LABELS[type]}
                        </span>
                    );
                },
            }),
            columnHelper.display({
                id: 'code',
                header: 'Code',
                cell: ({ row }) => (
                    <span
                        className="font-mono text-xs"
                        style={{ color: row.original.code ? 'var(--accent-info)' : 'var(--text-disabled)' }}
                    >
                        {row.original.code ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'description',
                header: 'Description',
                cell: ({ row }) => (
                    <span
                        className="max-w-[220px] truncate block text-sm"
                        style={{ color: 'var(--text-secondary)' }}
                        title={row.original.description ?? undefined}
                    >
                        {row.original.description ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'status',
                header: 'Status',
                cell: ({ row }) => {
                    const isDeleted = Boolean(row.original.deleted_at);
                    return (
                        <span
                            className="inline-flex rounded-full px-3 py-1 text-xs font-semibold"
                            style={{
                                color: isDeleted ? 'var(--accent-error)' : 'var(--accent-success)',
                                background: isDeleted
                                    ? 'color-mix(in srgb, var(--accent-error) 15%, transparent)'
                                    : 'color-mix(in srgb, var(--accent-success) 15%, transparent)',
                                border: isDeleted
                                    ? '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)'
                                    : '1px solid color-mix(in srgb, var(--accent-success) 25%, transparent)',
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
                    <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
                        {formatDateShort(row.original.created_at)}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'actions',
                header: 'Actions',
                cell: (info) => {
                    const zone = info.row.original;
                    const isDeleted = Boolean(zone.deleted_at);

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/zones/${zone.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View zone"
                                aria-label="View zone"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/zones/${zone.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit zone"
                                        aria-label="Edit zone"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(zone.uuid, zone.zone_name)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete zone"
                                        aria-label="Delete zone"
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
                                    onClick={() => onRestoreClick(zone.uuid, zone.zone_name)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore zone"
                                    aria-label="Restore zone"
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
            noDataMessage="No zones found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
