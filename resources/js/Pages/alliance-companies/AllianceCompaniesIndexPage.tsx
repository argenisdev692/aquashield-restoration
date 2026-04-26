import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import {
    useBulkDeleteAllianceCompanies,
    useDeleteAllianceCompany,
    useRestoreAllianceCompany,
} from '@/modules/alliance-companies/hooks/useAllianceCompanyMutations';
import { useAllianceCompanies } from '@/modules/alliance-companies/hooks/useAllianceCompanies';
import type { AllianceCompanyFilters } from '@/modules/alliance-companies/types';
import AppLayout from '@/pages/layouts/AppLayout';
import AllianceCompaniesTable from './components/AllianceCompaniesTable';

function buildVisiblePages(currentPage: number, lastPage: number): number[] {
    const end = Math.min(lastPage, Math.max(5, currentPage + 2));
    const start = Math.max(1, end - 4);

    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
}

export default function AllianceCompaniesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<AllianceCompanyFilters>(
        {
            page: 1,
            per_page: 15,
        },
        'alliance-companies-filters',
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

    const { data, isPending, isError } = useAllianceCompanies(filters);
    const deleteAllianceCompany = useDeleteAllianceCompany();
    const restoreAllianceCompany = useRestoreAllianceCompany();
    const bulkDeleteAllianceCompanies = useBulkDeleteAllianceCompanies();

    const allianceCompanies = data?.data ?? [];
    const meta = data?.meta ?? {
        current_page: 1,
        last_page: 1,
        per_page: 15,
        total: 0,
    };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;
    const visiblePages = buildVisiblePages(meta.current_page, meta.last_page);

    function handleSearchChange(value: string): void {
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
        if (pendingDelete === null) {
            return;
        }

        await deleteAllianceCompany.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) {
            return;
        }

        await restoreAllianceCompany.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) {
            return;
        }

        await bulkDeleteAllianceCompanies.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function goToPage(page: number): void {
        setFilters((current) => ({
            ...current,
            page,
        }));
    }

    return (
        <>
            <Head title="Alliance Companies" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1 className="text-3xl font-extrabold" style={{ color: 'var(--text-primary)' }}>
                                Alliance Companies
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <Link
                            href="/alliance-companies/create"
                            prefetch
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New alliance company</span>
                        </Link>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search alliance companies..."
                        searchAriaLabel="Search alliance companies"
                        statusValue={filters.status ?? ''}
                        onStatusChange={(value) => {
                            startTransition(() => {
                                setFilters((current) => ({
                                    ...current,
                                    status: value === '' ? undefined : value as 'active' | 'deleted',
                                    page: 1,
                                }));
                            });
                        }}
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startTransition(() => {
                                setFilters((current) => ({
                                    ...current,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                    />

                    <DataTableBulkActions
                        count={selectedCount}
                        onDelete={() => {
                            void handleBulkDelete();
                        }}
                        isDeleting={bulkDeleteAllianceCompanies.isPending}
                    />

                    <div className="card overflow-hidden p-0">
                        <AllianceCompaniesTable
                            data={allianceCompanies}
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

                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page <= 1}
                                        className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-xl p-0"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={16} />
                                    </button>

                                    {visiblePages.map((page) => (
                                        <button
                                            key={page}
                                            type="button"
                                            onClick={() => goToPage(page)}
                                            className="inline-flex h-9 min-w-9 items-center justify-center rounded-xl px-3 text-sm font-semibold"
                                            style={{
                                                background: page === meta.current_page
                                                    ? 'var(--accent-primary)'
                                                    : 'var(--bg-surface)',
                                                color: page === meta.current_page
                                                    ? 'var(--text-primary)'
                                                    : 'var(--text-secondary)',
                                                border: '1px solid var(--border-default)',
                                            }}
                                            aria-label={`Go to page ${page}`}
                                        >
                                            {page}
                                        </button>
                                    ))}

                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page + 1)}
                                        disabled={meta.current_page >= meta.last_page}
                                        className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-xl p-0"
                                        aria-label="Next page"
                                    >
                                        <ChevronRight size={16} />
                                    </button>
                                </div>
                            </div>
                        ) : null}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={() => {
                        void handleConfirmDelete();
                    }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteAllianceCompany.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="alliance company"
                    entityName={pendingRestore?.name}
                    onConfirm={() => {
                        void handleConfirmRestore();
                    }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreAllianceCompany.isPending}
                />
            </AppLayout>
        </>
    );
}
