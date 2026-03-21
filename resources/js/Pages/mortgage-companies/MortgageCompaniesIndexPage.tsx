import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus, Search } from 'lucide-react';
import { ExportButton } from '@/common/export/ExportButton';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import {
    useBulkDeleteMortgageCompanies,
    useDeleteMortgageCompany,
    useRestoreMortgageCompany,
} from '@/modules/mortgage-companies/hooks/useMortgageCompanyMutations';
import { useMortgageCompanies } from '@/modules/mortgage-companies/hooks/useMortgageCompanies';
import type { MortgageCompanyFilters } from '@/modules/mortgage-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import MortgageCompaniesTable from './components/MortgageCompaniesTable';

function buildVisiblePages(currentPage: number, lastPage: number): number[] {
    const end = Math.min(lastPage, Math.max(5, currentPage + 2));
    const start = Math.max(1, end - 4);

    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
}

export default function MortgageCompaniesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<MortgageCompanyFilters>(
        { page: 1, per_page: 15 },
        'mortgage-companies-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending, isError } = useMortgageCompanies(filters);
    const deleteMortgageCompany = useDeleteMortgageCompany();
    const restoreMortgageCompany = useRestoreMortgageCompany();
    const bulkDeleteMortgageCompanies = useBulkDeleteMortgageCompanies();

    const mortgageCompanies = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };
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

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deleteMortgageCompany.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreMortgageCompany.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) return;

        await bulkDeleteMortgageCompanies.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Mortgage Companies" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                Mortgage Companies
                            </h1>
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <Link
                            href="/mortgage-companies/create"
                            className="btn-primary inline-flex items-center gap-2 rounded-xl px-4 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New mortgage company</span>
                        </Link>
                    </div>

                    <div className="flex flex-col gap-3 sm:flex-row sm:items-center">
                        <div
                            className="flex flex-1 items-center gap-2 rounded-xl px-3 py-2"
                            style={{
                                background: 'var(--bg-card)',
                                border: '1px solid var(--border-default)',
                            }}
                        >
                            <Search size={16} style={{ color: 'var(--text-muted)' }} />
                            <input
                                type="search"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Search by name, email, phone..."
                                className="flex-1 bg-transparent text-sm outline-none"
                                style={{ color: 'var(--text-primary)' }}
                            />
                        </div>

                        <DataTableDateRangeFilter
                            dateFrom={filters.date_from}
                            dateTo={filters.date_to}
                            onChange={(range) =>
                                startTransition(() => {
                                    setFilters((current) => ({
                                        ...current,
                                        date_from: range.dateFrom,
                                        date_to: range.dateTo,
                                        page: 1,
                                    }));
                                })
                            }
                        />

                        <select
                            value={filters.status ?? 'all'}
                            onChange={(event) => {
                                const value = event.target.value;
                                startTransition(() => {
                                    setFilters((current) => ({
                                        ...current,
                                        status: value === 'all' ? undefined : value,
                                        page: 1,
                                    }));
                                });
                            }}
                            className="rounded-xl px-3 py-2 text-sm outline-none"
                            style={{
                                background: 'var(--bg-card)',
                                border: '1px solid var(--border-default)',
                                color: 'var(--text-primary)',
                            }}
                        >
                            <option value="all">All status</option>
                            <option value="active">Active</option>
                            <option value="deleted">Deleted</option>
                        </select>

                        <div className="h-6 w-px" style={{ background: 'var(--border-subtle)' }} />

                        <ExportButton
                            onExport={(format) => {
                                startExportTransition(() => {
                                    const params = new URLSearchParams({ format });
                                    if (filters.search) params.append('search', filters.search);
                                    if (filters.status) params.append('status', filters.status);
                                    if (filters.date_from) params.append('date_from', filters.date_from);
                                    if (filters.date_to) params.append('date_to', filters.date_to);
                                    window.open(`/mortgage-companies/data/admin/export?${params.toString()}`, '_blank');
                                });
                            }}
                            isExporting={isPendingExport}
                        />
                    </div>

                    {selectedCount > 0 ? (
                        <DataTableBulkActions
                            selectedCount={selectedCount}
                            onBulkDelete={() => {
                                void handleBulkDelete();
                            }}
                            isBulkDeleting={bulkDeleteMortgageCompanies.isPending}
                        />
                    ) : null}

                    <MortgageCompaniesTable
                        data={mortgageCompanies}
                        isPending={isPending}
                        isError={isError}
                        onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                        onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
                        rowSelection={rowSelection}
                        onRowSelectionChange={setRowSelection}
                    />

                    {meta.last_page > 1 ? (
                        <div className="flex items-center justify-between">
                            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                Page {meta.current_page} of {meta.last_page}
                            </p>
                            <div className="flex items-center gap-1">
                                <button
                                    type="button"
                                    onClick={() => goToPage(meta.current_page - 1)}
                                    disabled={meta.current_page <= 1}
                                    className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-xl p-0 disabled:opacity-40"
                                    aria-label="Previous page"
                                >
                                    <ChevronLeft size={16} />
                                </button>

                                {visiblePages.map((page) => (
                                    <button
                                        key={page}
                                        type="button"
                                        onClick={() => goToPage(page)}
                                        className="inline-flex h-9 w-9 items-center justify-center rounded-xl p-0 text-sm font-semibold"
                                        style={{
                                            background: page === meta.current_page ? 'var(--accent-primary)' : 'transparent',
                                            color: page === meta.current_page ? '#fff' : 'var(--text-muted)',
                                        }}
                                    >
                                        {page}
                                    </button>
                                ))}

                                <button
                                    type="button"
                                    onClick={() => goToPage(meta.current_page + 1)}
                                    disabled={meta.current_page >= meta.last_page}
                                    className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-xl p-0 disabled:opacity-40"
                                    aria-label="Next page"
                                >
                                    <ChevronRight size={16} />
                                </button>
                            </div>
                        </div>
                    ) : null}
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={() => {
                        void handleConfirmDelete();
                    }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteMortgageCompany.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="mortgage company"
                    entityName={pendingRestore?.name}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreMortgageCompany.isPending}
                />
            </AppLayout>
        </>
    );
}
