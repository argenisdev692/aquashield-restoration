import * as React from 'react';
import { Link } from '@inertiajs/react';
import {
    createColumnHelper,
    type ColumnDef,
    type OnChangeFn,
    type RowSelectionState,
} from '@tanstack/react-table';
import { CheckCircle, Eye, Pencil, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { InsuranceCompany } from '@/modules/insurance-companies/types';

interface InsuranceCompaniesTableProps {
    data: InsuranceCompany[];
    isLoading: boolean;
    isError: boolean;
    onDelete: (uuid: string, name: string) => void;
    onRestore: (uuid: string, name: string) => void;
    rowSelection: RowSelectionState;
    onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

const columnHelper = createColumnHelper<InsuranceCompany>();

export default function InsuranceCompaniesTable({
    data,
    isLoading,
    isError,
    onDelete,
    onRestore,
    rowSelection,
    onRowSelectionChange,
}: InsuranceCompaniesTableProps): React.JSX.Element {
    const columns = React.useMemo<ColumnDef<InsuranceCompany, unknown>[]>(() => [
        columnHelper.display({
            id: 'select',
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                    aria-label="Select all insurance companies"
                    style={{ accentColor: 'var(--accent-primary)' }}
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                    aria-label={`Select ${row.original.insurance_company_name}`}
                    disabled={row.original.deleted_at !== null}
                    style={{ accentColor: 'var(--accent-primary)' }}
                />
            ),
        }),
        columnHelper.accessor('insurance_company_name', {
            header: 'Company Name',
            cell: (info) => (
                <span className="font-semibold" style={{ color: 'var(--text-primary)' }}>
                    {info.getValue()}
                </span>
            ),
        }),
        columnHelper.accessor('email', {
            header: 'Email',
            cell: (info) => info.getValue() ?? '—',
        }),
        columnHelper.accessor('phone', {
            header: 'Phone',
            cell: (info) => info.getValue() ?? '—',
        }),
        columnHelper.accessor('website', {
            header: 'Website',
            cell: (info) => {
                const url = info.getValue();

                if (!url) {
                    return '—';
                }

                return (
                    <a
                        href={url}
                        target="_blank"
                        rel="noreferrer"
                        style={{ color: 'var(--accent-primary)' }}
                        className="font-medium hover:underline"
                    >
                        Visit
                    </a>
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
        columnHelper.display({
            id: 'actions',
            header: 'Actions',
            cell: ({ row }) => {
                const company = row.original;
                const isDeleted = company.deleted_at !== null;

                return (
                    <div className="flex items-center justify-center gap-2">
                        <Link
                            href={`/insurance-companies/${company.uuid}`}
                            prefetch
                            aria-label={`View ${company.insurance_company_name}`}
                            title="View"
                            className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                        >
                            <Eye size={16} />
                        </Link>

                        {isDeleted ? (
                            <button
                                type="button"
                                onClick={() => onRestore(company.uuid, company.insurance_company_name)}
                                aria-label={`Restore ${company.insurance_company_name}`}
                                title="Restore"
                                className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                                style={{ color: 'var(--accent-success)' }}
                            >
                                <CheckCircle size={16} />
                            </button>
                        ) : (
                            <>
                                <Link
                                    href={`/insurance-companies/${company.uuid}/edit`}
                                    prefetch
                                    aria-label={`Edit ${company.insurance_company_name}`}
                                    title="Edit"
                                    className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                                >
                                    <Pencil size={16} />
                                </Link>
                                <button
                                    type="button"
                                    onClick={() => onDelete(company.uuid, company.insurance_company_name)}
                                    aria-label={`Delete ${company.insurance_company_name}`}
                                    title="Delete"
                                    className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg"
                                    style={{ color: 'var(--accent-error)' }}
                                >
                                    <Trash2 size={16} />
                                </button>
                            </>
                        )}
                    </div>
                );
            },
        }),
    ], [onDelete, onRestore]);

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isLoading}
            isError={isError}
            noDataMessage="No insurance companies found"
            getRowId={(row) => row.uuid}
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
