import * as React from 'react';
import { createColumnHelper, type ColumnDef, type RowSelectionState, type OnChangeFn } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { DataTable } from '@/shadcn/data-table';
import type { MortgageCompanyListItem } from '@/types/api';
import { formatDateShort } from '@/utils/dateFormatter';
import { Eye, Pencil, Trash2, RotateCcw } from 'lucide-react';

interface MortgageCompaniesTableProps {
    data: MortgageCompanyListItem[];
    isLoading: boolean;
    isError: boolean;
    onDelete: (uuid: string, name: string) => void;
    onRestore: (uuid: string, name: string) => void;
    rowSelection?: RowSelectionState;
    onRowSelectionChange?: OnChangeFn<RowSelectionState>;
}

export default function MortgageCompaniesTable({
    data,
    isLoading,
    isError,
    onDelete,
    onRestore,
    rowSelection,
    onRowSelectionChange,
}: MortgageCompaniesTableProps) {
    const columnHelper = createColumnHelper<MortgageCompanyListItem>();

    const columns = React.useMemo<ColumnDef<MortgageCompanyListItem, any>[]>(() => [
        columnHelper.display({
            id: 'select',
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                    aria-label="Select all"
                    className="h-4 w-4 rounded cursor-pointer"
                    style={{
                        accentColor: 'var(--accent-primary)',
                        border: '1px solid var(--border-default)',
                    }}
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                    aria-label="Select row"
                    className="h-4 w-4 rounded cursor-pointer"
                    style={{
                        accentColor: 'var(--accent-primary)',
                        border: '1px solid var(--border-default)',
                    }}
                />
            ),
        }),
        columnHelper.accessor('mortgageCompanyName', {
            header: 'Company Name',
            cell: (info) => (
                <span 
                    className="font-semibold"
                    style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                >
                    {info.getValue()}
                </span>
            ),
        }),
        columnHelper.accessor('email', {
            header: 'Email',
            cell: (info) => (
                <span style={{ color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                    {info.getValue() || '—'}
                </span>
            ),
        }),
        columnHelper.accessor('phone', {
            header: 'Phone',
            cell: (info) => (
                <span style={{ color: 'var(--text-secondary)', fontFamily: 'var(--font-sans)' }}>
                    {info.getValue() || '—'}
                </span>
            ),
        }),
        columnHelper.accessor('website', {
            header: 'Website',
            cell: (info) => {
                const url = info.getValue();
                if (!url) return <span style={{ color: 'var(--text-disabled)' }}>—</span>;
                return (
                    <a 
                        href={url} 
                        target="_blank" 
                        rel="noopener noreferrer"
                        className="font-medium hover:underline"
                        style={{ color: 'var(--accent-primary)', fontFamily: 'var(--font-sans)' }}
                    >
                        Visit
                    </a>
                );
            },
        }),
        columnHelper.accessor('createdAt', {
            header: 'Created',
            cell: (info) => (
                <span 
                    className="text-sm"
                    style={{ color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}
                >
                    {formatDateShort(info.getValue())}
                </span>
            ),
        }),
        columnHelper.display({
            id: 'actions',
            header: 'Actions',
            cell: (info) => {
                const company = info.row.original;
                const isDeleted = !!company.deletedAt;

                return (
                    <div className="flex items-center justify-center gap-1.5">
                        <Link 
                            href={`/mortgage-companies/${company.uuid}`}
                            className="btn-action btn-action-view"
                            title="View Company"
                        >
                            <Eye size={14} />
                        </Link>
                        
                        {!isDeleted ? (
                            <>
                                <Link 
                                    href={`/mortgage-companies/${company.uuid}/edit`}
                                    className="btn-action btn-action-edit"
                                    title="Edit Company"
                                >
                                    <Pencil size={14} />
                                </Link>
                                <button 
                                    onClick={() => onDelete(company.uuid, company.mortgageCompanyName)}
                                    className="btn-action btn-action-delete"
                                    title="Delete Company"
                                >
                                    <Trash2 size={14} />
                                </button>
                            </>
                        ) : (
                            <button 
                                onClick={() => onRestore(company.uuid, company.mortgageCompanyName)}
                                className="btn-action btn-action-restore"
                                title="Restore Company"
                            >
                                <RotateCcw size={14} />
                            </button>
                        )}
                    </div>
                );
            },
        }),
    ], [columnHelper, onDelete, onRestore]);

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isLoading}
            isError={isError}
            noDataMessage="No mortgage companies found"
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
