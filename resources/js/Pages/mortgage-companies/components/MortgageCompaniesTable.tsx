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
import type { MortgageCompany } from '@/modules/mortgage-companies/types';

interface MortgageCompaniesTableProps {
    data: MortgageCompany[];
    isPending: boolean;
    isError: boolean;
    onDeleteClick: (uuid: string, name: string) => void;
    onRestoreClick: (uuid: string, name: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<MortgageCompany>();

export default function MortgageCompaniesTable({
    data,
    isPending,
    isError,
    onDeleteClick,
    onRestoreClick,
    rowSelection,
    onRowSelectionChange,
}: MortgageCompaniesTableProps): React.JSX.Element {
    const columns = React.useMemo(
        () => [
            columnHelper.display({
                id: 'select',
                header: ({ table }) => (
                    <input
                        type="checkbox"
                        checked={table.getIsAllPageRowsSelected()}
                        onChange={table.getToggleAllPageRowsSelectedHandler()}
                        aria-label="Select all mortgage companies"
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
                cell: ({ row }) => (
                    <input
                        type="checkbox"
                        checked={row.getIsSelected()}
                        onChange={row.getToggleSelectedHandler()}
                        aria-label={`Select ${row.original.mortgage_company_name}`}
                        className="h-4 w-4 cursor-pointer rounded"
                        style={{ accentColor: 'var(--accent-primary)' }}
                    />
                ),
            }),
            columnHelper.display({
                id: 'mortgage_company_name',
                header: 'Company',
                cell: ({ row }) => (
                    <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                        {row.original.mortgage_company_name}
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
                        return <span style={{ color: 'var(--text-secondary)' }}>—</span>;
                    }

                    return (
                        <a
                            href={row.original.website}
                            target="_blank"
                            rel="noreferrer"
                            title={`Open ${row.original.mortgage_company_name} website`}
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
                    const company = row.original;
                    const isDeleted = company.deleted_at !== null;

                    return (
                        <div className="flex items-center justify-center gap-2">
                            <Link
                                href={`/mortgage-companies/${company.uuid}`}
                                prefetch
                                className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                title="View mortgage company"
                                aria-label="View mortgage company"
                            >
                                <Eye size={14} />
                            </Link>

                            {!isDeleted ? (
                                <>
                                    <Link
                                        href={`/mortgage-companies/${company.uuid}/edit`}
                                        prefetch
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Edit mortgage company"
                                        aria-label="Edit mortgage company"
                                    >
                                        <Pencil size={14} />
                                    </Link>
                                    <button
                                        type="button"
                                        onClick={() => onDeleteClick(company.uuid, company.mortgage_company_name)}
                                        className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                        title="Delete mortgage company"
                                        aria-label="Delete mortgage company"
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
                                    onClick={() => onRestoreClick(company.uuid, company.mortgage_company_name)}
                                    className="inline-flex h-8 w-8 items-center justify-center rounded-lg p-0"
                                    title="Restore mortgage company"
                                    aria-label="Restore mortgage company"
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
            noDataMessage="No mortgage companies found."
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
