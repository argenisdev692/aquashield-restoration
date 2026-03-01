import * as React from 'react';
import { createColumnHelper, type ColumnDef, type RowSelectionState, type OnChangeFn } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { DataTable } from '@/shadcn/data-table';
import { PublicCompany } from '@/modules/public-companies/types';
import { usePublicCompanyMutations } from '@/modules/public-companies/hooks/usePublicCompanyMutations';
import { formatDateShort } from '@/utils/dateFormatter';
import { Eye, Pencil, Trash2, RotateCcw } from 'lucide-react';

interface PublicCompaniesTableProps {
    data: PublicCompany[];
    isLoading: boolean;
    isError: boolean;
    onDelete: (uuid: string, name: string) => void;
    rowSelection?: RowSelectionState;
    onRowSelectionChange?: OnChangeFn<RowSelectionState>;
}

export default function PublicCompaniesTable({
    data,
    isLoading,
    isError,
    onDelete,
    rowSelection,
    onRowSelectionChange,
}: PublicCompaniesTableProps) {
    const columnHelper = createColumnHelper<PublicCompany>();
    const { restorePublicCompany } = usePublicCompanyMutations();

    const columns = React.useMemo<ColumnDef<PublicCompany, any>[]>(() => [
        columnHelper.display({
            id: 'select',
            header: ({ table }) => (
                <input
                    type="checkbox"
                    checked={table.getIsAllPageRowsSelected()}
                    onChange={table.getToggleAllPageRowsSelectedHandler()}
                    aria-label="Select all"
                    className="h-4 w-4 rounded border-gray-300 accent-(--accent-primary) cursor-pointer"
                />
            ),
            cell: ({ row }) => (
                <input
                    type="checkbox"
                    checked={row.getIsSelected()}
                    onChange={row.getToggleSelectedHandler()}
                    aria-label="Select row"
                    className="h-4 w-4 rounded border-gray-300 accent-(--accent-primary) cursor-pointer"
                />
            ),
        }),
        columnHelper.accessor('public_company_name', {
            header: 'Company Name',
            cell: (info) => <span className="font-semibold text-(--text-primary)">{info.getValue()}</span>,
        }),
        columnHelper.accessor('email', {
            header: 'Email',
            cell: (info) => info.getValue() || '-',
        }),
        columnHelper.accessor('phone', {
            header: 'Phone',
            cell: (info) => info.getValue() || '-',
        }),
        columnHelper.accessor('unit', {
            header: 'Unit',
            cell: (info) => info.getValue() || '-',
        }),
        columnHelper.accessor('website', {
            header: 'Website',
            cell: (info) => {
                const url = info.getValue();
                if (!url) return '-';
                return (
                    <a 
                        href={url} 
                        target="_blank" 
                        rel="noopener noreferrer"
                        className="text-(--accent-primary) hover:underline font-medium"
                    >
                        Visit
                    </a>
                );
            },
        }),
        columnHelper.accessor('created_at', {
            header: 'Created',
            cell: (info) => (
                <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
                    {formatDateShort(info.getValue())}
                </span>
            ),
        }),
        columnHelper.display({
            id: 'actions',
            header: 'Actions',
            cell: (info) => {
                const company = info.row.original;
                const isDeleted = !!company.deleted_at;

                return (
                    <div className="flex items-center justify-center gap-1.5">
                        <Link 
                            href={`/public-companies/${company.uuid}`}
                            className="btn-action btn-action-view"
                            title="View Carrier"
                        >
                            <Eye size={14} />
                        </Link>
                        
                        {!isDeleted ? (
                            <>
                                <Link 
                                    href={`/public-companies/${company.uuid}/edit`}
                                    className="btn-action btn-action-edit"
                                    title="Edit Carrier"
                                >
                                    <Pencil size={14} />
                                </Link>
                                <button 
                                    onClick={() => onDelete(company.uuid, company.public_company_name)}
                                    className="btn-action btn-action-delete"
                                    title="Delete Carrier"
                                >
                                    <Trash2 size={14} />
                                </button>
                            </>
                        ) : (
                            <button 
                                onClick={() => restorePublicCompany.mutate(company.uuid)}
                                className="btn-action btn-action-restore"
                                title="Restore Carrier"
                                disabled={restorePublicCompany.isPending}
                            >
                                <RotateCcw size={14} />
                            </button>
                        )}
                    </div>
                );
            },
        }),
    ], [columnHelper, onDelete, restorePublicCompany]);

    return (
        <DataTable
            columns={columns}
            data={data}
            isLoading={isLoading}
            isError={isError}
            noDataMessage="No Public companies found"
            rowSelection={rowSelection}
            onRowSelectionChange={onRowSelectionChange}
        />
    );
}
