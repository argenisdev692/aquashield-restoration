import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus, Search } from 'lucide-react';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/common/export/ExportButton';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import {
    useBulkDeleteCustomers,
    useDeleteCustomer,
    useRestoreCustomer,
} from '@/modules/customers/hooks/useCustomerMutations';
import { useCustomers } from '@/modules/customers/hooks/useCustomers';
import type { CustomerFilters } from '@/modules/customers/types';
import AppLayout from '@/pages/layouts/AppLayout';
import CustomersTable from './components/CustomersTable';

function buildVisiblePages(currentPage: number, lastPage: number): number[] {
    const end = Math.min(lastPage, Math.max(5, currentPage + 2));
    const start = Math.max(1, end - 4);
    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
}

export default function CustomersIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<CustomerFilters>(
        { page: 1, per_page: 15 },
        'customers-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{
        uuid: string;
        name: string;
    } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{
        uuid: string;
        name: string;
    } | null>(null);
    const [, startTransition] = React.useTransition();
    const [, startExportTransition] = React.useTransition();

    const { data, isPending, isError } = useCustomers(filters);
    const deleteCustomer = useDeleteCustomer();
    const restoreCustomer = useRestoreCustomer();
    const bulkDeleteCustomers = useBulkDeleteCustomers();

    const customers = data?.data ?? [];
    const meta = data?.meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;
    const visiblePages = buildVisiblePages(meta.current_page, meta.last_page);

    function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const value = event.target.value;
        setSearch(value);
        startTransition(() => {
            setFilters((current) => ({
                ...current,
                search: value === '' ? undefined : value,
                page: 1,
            }));
        });
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams({ format });
            if (filters.search) params.append('search', filters.search);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.status) params.append('status', filters.status);
            window.open(`/customers/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deleteCustomer.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreCustomer.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);
        if (selectedUuids.length === 0) return;
        await bulkDeleteCustomers.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Customers" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                Customers
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_CUSTOMER']}>
                            <Link
                                href="/customers/create"
                                prefetch
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New customer</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    <div
                        className="flex flex-col gap-4 rounded-3xl px-5 py-4 shadow-sm lg:flex-row lg:items-end lg:justify-between"
                        style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', fontFamily: 'var(--font-sans)' }}
                    >
                        <div
                            className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3"
                            style={{ background: 'var(--bg-surface)' }}
                        >
                            <Search size={16} style={{ color: 'var(--text-muted)' }} />
                            <input
                                type="text"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Search customers..."
                                className="w-full bg-transparent text-sm outline-none"
                                style={{
                                    color: 'var(--text-primary)',
                                    fontFamily: 'var(--font-sans)',
                                }}
                            />
                        </div>

                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-end">
                            <select
                                value={filters.status ?? ''}
                                onChange={(event) =>
                                    setFilters((current) => ({
                                        ...current,
                                        status:
                                            event.target.value === ''
                                                ? undefined
                                                : (event.target.value as 'active' | 'deleted'),
                                        page: 1,
                                    }))
                                }
                                className="rounded-xl px-4 py-3 text-sm outline-none"
                                style={{
                                    border: '1px solid var(--border-default)',
                                    background: 'var(--bg-surface)',
                                    color: 'var(--text-primary)',
                                    fontFamily: 'var(--font-sans)',
                                }}
                            >
                                <option value="">All status</option>
                                <option value="active">Active</option>
                                <option value="deleted">Deleted</option>
                            </select>

                            <DataTableDateRangeFilter
                                dateFrom={filters.date_from}
                                dateTo={filters.date_to}
                                onChange={(range) =>
                                    setFilters((current) => ({
                                        ...current,
                                        date_from: range.dateFrom,
                                        date_to: range.dateTo,
                                        page: 1,
                                    }))
                                }
                            />

                            <PermissionGuard permissions={['VIEW_CUSTOMER']}>
                                <ExportButton onExport={handleExport} />
                            </PermissionGuard>
                        </div>
                    </div>

                    <PermissionGuard permissions={['DELETE_CUSTOMER']}>
                        <DataTableBulkActions
                            count={selectedCount}
                            onDelete={() => { void handleBulkDelete(); }}
                            isDeleting={bulkDeleteCustomers.isPending}
                        />
                    </PermissionGuard>

                    <div className="card overflow-hidden p-0">
                        <CustomersTable
                            data={customers}
                            isPending={isPending}
                            isError={isError}
                            onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.last_page > 1 ? (
                            <div
                                className="flex flex-col gap-4 border-t px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                                style={{ borderColor: 'var(--border-default)' }}
                            >
                                <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Page {meta.current_page} of {meta.last_page}
                                </span>

                                <div className="flex items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page <= 1}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={14} />
                                    </button>

                                    {visiblePages.map((page) => (
                                        <button
                                            key={page}
                                            type="button"
                                            onClick={() => goToPage(page)}
                                            className="inline-flex h-8 w-8 items-center justify-center rounded-lg text-sm font-medium transition-all"
                                            style={
                                                page === meta.current_page
                                                    ? {
                                                          background: 'var(--accent-primary)',
                                                          color: '#fff',
                                                      }
                                                    : {
                                                          color: 'var(--text-secondary)',
                                                          background: 'transparent',
                                                      }
                                            }
                                            aria-label={`Go to page ${page}`}
                                            aria-current={page === meta.current_page ? 'page' : undefined}
                                        >
                                            {page}
                                        </button>
                                    ))}

                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page + 1)}
                                        disabled={meta.current_page >= meta.last_page}
                                        className="btn-ghost inline-flex h-8 w-8 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Next page"
                                    >
                                        <ChevronRight size={14} />
                                    </button>
                                </div>
                            </div>
                        ) : null}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteCustomer.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="customer"
                    entityName={pendingRestore?.name}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreCustomer.isPending}
                />
            </AppLayout>
        </>
    );
}
