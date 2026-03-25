import * as React from 'react';
import { Link } from '@inertiajs/react';
import {
    createColumnHelper,
    type OnChangeFn,
    type RowSelectionState,
} from '@tanstack/react-table';
import { CheckCircle, Eye, Pencil, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { PropertyListItem } from '@/modules/properties/types';

interface PropertiesTableProps {
    data: PropertyListItem[];
    isPending: boolean;
    isError: boolean;
    onDeleteClick: (uuid: string, address: string) => void;
    onRestoreClick: (uuid: string, address: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<PropertyListItem>();

export default function PropertiesTable({
    data,
    isPending,
    isError,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: PropertiesTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: 'select',
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all properties"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label={`Select ${row.original.property_address}`}
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
            }),
            columnHelper.display({
                id: 'property_address',
                header: 'Address',
                cell: ({ row }) => (
                    <div className="flex flex-col gap-0.5">
                        <span
                            className="font-semibold"
                            style={{ color: 'var(--text-primary)' }}
                        >
                            {row.original.property_address}
                        </span>
                        {row.original.property_address_2 !== null ? (
                            <span className="text-xs" style={{ color: 'var(--text-muted)' }}>
                                {row.original.property_address_2}
                            </span>
                        ) : null}
                    </div>
                ),
            }),
            columnHelper.display({
                id: 'property_city',
                header: 'City',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-secondary)' }}>
                        {row.original.property_city ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'property_state',
                header: 'State',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-secondary)' }}>
                        {row.original.property_state ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'property_country',
                header: 'Country',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-secondary)' }}>
                        {row.original.property_country ?? '—'}
                    </span>
                ),
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
                cell: ({ row }) => {
                    const property = row.original;
                    const isDeleted = property.deleted_at !== null;

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/properties/${property.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View property"
                                aria-label="View property"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/properties/${property.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit property"
                                        aria-label="Edit property"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() =>
                                            onDeleteClick(property.uuid, property.property_address)
                                        }
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete property"
                                        aria-label="Delete property"
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
                                    onClick={() =>
                                        onRestoreClick(property.uuid, property.property_address)
                                    }
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore property"
                                    aria-label="Restore property"
                                    style={{
                                        color: 'var(--accent-success)',
                                        border: '1px solid color-mix(in srgb, var(--accent-success) 30%, var(--border-default))',
                                        background: 'color-mix(in srgb, var(--accent-success) 10%, transparent)',
                                    }}
                                >
                                    <CheckCircle size={14} />
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
            isError={isError}
            noDataMessage="No properties found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
