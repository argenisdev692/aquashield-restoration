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
import type { AllianceCompany } from '@/modules/alliance-companies/types';

interface AllianceCompaniesTableProps {
    data: AllianceCompany[];
    isPending: boolean;
    isError: boolean;
    onDeleteClick: (uuid: string, allianceCompanyName: string) => void;
    onRestoreClick: (uuid: string, allianceCompanyName: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<AllianceCompany>();

export default function AllianceCompaniesTable({
    data,
    isPending,
    isError,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: AllianceCompaniesTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: 'select',
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all alliance companies"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label={`Select ${row.original.alliance_company_name}`}
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
            }),
            columnHelper.display({
                id: 'alliance_company_name',
                header: 'Company',
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                        {row.original.alliance_company_name}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'email',
                header: 'Email',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-secondary)' }}>
                        {row.original.email ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'phone',
                header: 'Phone',
                cell: ({ row }) => (
                    <span style={{ color: 'var(--text-secondary)' }}>
                        {row.original.phone ?? '—'}
                    </span>
                ),
            }),
            columnHelper.display({
                id: 'website',
                header: 'Website',
                cell: ({ row }) => {
                    if (row.original.website === null) {
                        return (
                            <span style={{ color: 'var(--text-secondary)' }}>
                                —
                            </span>
                        );
                    }

                    return (
                        <a
                            href={row.original.website}
                            target="_blank"
                            rel="noreferrer"
                            title={`Open ${row.original.alliance_company_name} website`}
                            style={{ color: 'var(--accent-primary)' }}
                            className="font-semibold hover:underline"
                        >
                            Visit
                        </a>
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
                cell: ({ row }) => {
                    const allianceCompany = row.original;
                    const isDeleted = allianceCompany.deleted_at !== null;

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/alliance-companies/${allianceCompany.uuid}`}
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View alliance company"
                                aria-label="View alliance company"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/alliance-companies/${allianceCompany.uuid}/edit`}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit alliance company"
                                        aria-label="Edit alliance company"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(allianceCompany.uuid, allianceCompany.alliance_company_name)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete alliance company"
                                        aria-label="Delete alliance company"
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
                                    onClick={() => onRestoreClick(allianceCompany.uuid, allianceCompany.alliance_company_name)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore alliance company"
                                    aria-label="Restore alliance company"
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
            noDataMessage="No alliance companies found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
