import * as React from 'react';
import { Link } from '@inertiajs/react';
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from '@tanstack/react-table';
import { Eye, Pencil, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { FileEsx } from '@/modules/files-esx/types';

interface FilesEsxTableProps {
    data: FileEsx[];
    isLoading: boolean;
    isError: boolean;
    onDelete: (uuid: string, name: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<FileEsx>();

const columns = [
    columnHelper.display({
        id: 'select',
        header: ({ table }) => (
            <input
                type="checkbox"
                checked={table.getIsAllPageRowsSelected()}
                onChange={table.getToggleAllPageRowsSelectedHandler()}
                aria-label="Select all files ESX"
                style={{ accentColor: 'var(--accent-primary)' }}
            />
        ),
        cell: ({ row }) => (
            <input
                type="checkbox"
                checked={row.getIsSelected()}
                onChange={row.getToggleSelectedHandler()}
                aria-label={`Select ${row.original.file_name ?? row.original.uuid}`}
                style={{ accentColor: 'var(--accent-primary)' }}
            />
        ),
    }),
    columnHelper.accessor('file_name', {
        header: 'File Name',
        cell: (info) => {
            const value = info.getValue();

            return value ? (
                <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                    {value}
                </span>
            ) : (
                <span style={{ color: 'var(--text-muted)' }}>—</span>
            );
        },
    }),
    columnHelper.accessor('file_path', {
        header: 'File Path',
        cell: (info) => (
            <span
                className="block max-w-[220px] truncate font-mono text-xs"
                style={{ color: 'var(--text-secondary)' }}
                title={info.getValue()}
            >
                {info.getValue()}
            </span>
        ),
    }),
    columnHelper.accessor('uploader', {
        header: 'Uploaded By',
        cell: (info) => {
            const uploader = info.getValue();

            return uploader ? (
                <span style={{ color: 'var(--text-secondary)' }}>{uploader.name}</span>
            ) : (
                <span style={{ color: 'var(--text-muted)' }}>—</span>
            );
        },
    }),
    columnHelper.accessor('assigned_adjusters', {
        header: 'Adjusters',
        cell: (info) => {
            const adjusters = info.getValue();

            if (adjusters.length === 0) {
                return <span style={{ color: 'var(--text-muted)' }}>—</span>;
            }

            return (
                <span
                    className="inline-flex items-center gap-1 rounded-full px-2 py-0.5 text-xs font-semibold"
                    style={{
                        background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                        color: 'var(--accent-primary)',
                    }}
                >
                    {adjusters.length} adjuster{adjusters.length !== 1 ? 's' : ''}
                </span>
            );
        },
    }),
    columnHelper.accessor('created_at', {
        header: 'Created',
        cell: (info) => (
            <span style={{ color: 'var(--text-muted)' }}>
                {formatDateShort(info.getValue())}
            </span>
        ),
    }),
];

export default function FilesEsxTable({
    data,
    isLoading,
    isError,
    onDelete,
    rowSelection,
    onRowSelectionChange,
}: FilesEsxTableProps): React.JSX.Element {
    const actionsColumn = React.useMemo(
        () => columnHelper.display({
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => {
                const file = row.original;
                const label = file.file_name ?? file.uuid;

                return (
                    <div className="flex items-center justify-center gap-2">
                        <Link
                            href={`/files-esx/${file.uuid}`}
                            prefetch
                            aria-label={`View ${label}`}
                            title="View"
                            className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <Eye size={16} />
                        </Link>
                        <Link
                            href={`/files-esx/${file.uuid}/edit`}
                            prefetch
                            aria-label={`Edit ${label}`}
                            title="Edit"
                            className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <Pencil size={16} />
                        </Link>
                        <button
                            type="button"
                            onClick={() => onDelete(file.uuid, label)}
                            aria-label={`Delete ${label} permanently`}
                            title="Delete permanently"
                            className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                            style={{ color: 'var(--accent-error)' }}
                        >
                            <Trash2 size={16} />
                        </button>
                    </div>
                );
            },
        }),
        [onDelete],
    );

    const allColumns = React.useMemo(
        () => [...columns, actionsColumn],
        [actionsColumn],
    );

    return (
        <DataTable
            columns={allColumns}
            data={data}
            isLoading={isLoading}
            isError={isError}
            noDataMessage="No files ESX found"
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
