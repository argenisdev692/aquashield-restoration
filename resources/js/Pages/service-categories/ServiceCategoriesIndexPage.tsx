import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import {
    useBulkDeleteServiceCategories,
    useDeleteServiceCategory,
    useRestoreServiceCategory,
} from '@/modules/service-categories/hooks/useServiceCategoryMutations';
import { useServiceCategories } from '@/modules/service-categories/hooks/useServiceCategories';
import type { ServiceCategoryFilters } from '@/modules/service-categories/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ServiceCategoriesTable from './components/ServiceCategoriesTable';

export default function ServiceCategoriesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<ServiceCategoryFilters>(
        { page: 1, per_page: 15 },
        'service-categories-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useServiceCategories(filters);
    const deleteServiceCategory = useDeleteServiceCategory();
    const restoreServiceCategory = useRestoreServiceCategory();
    const bulkDeleteServiceCategories = useBulkDeleteServiceCategories();

    const serviceCategories = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;

    function handleSearchChange(value: string): void {
        setSearch(value);
        startTransition(() => {
            setFilters((current) => ({ ...current, search: value === '' ? undefined : value, page: 1 }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deleteServiceCategory.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreServiceCategory.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) return;

        await bulkDeleteServiceCategories.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/service-categories/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Service Categories" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Service Categories
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <Link
                            href="/service-categories/create"
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New service category</span>
                        </Link>
                    </div>

                    <div className="card flex flex-col gap-4" style={{ fontFamily: 'var(--font-sans)' }}>
                        <CrudFilterBar
                            searchValue={search}
                            onSearchChange={handleSearchChange}
                            searchPlaceholder="Search service categories..."
                            searchAriaLabel="Search service categories"
                            statusValue={filters.status ?? ''}
                            onStatusChange={(value) => {
                                startTransition(() => {
                                    setFilters((current) => ({
                                        ...current,
                                        status: value === '' ? undefined : (value as 'active' | 'deleted'),
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
                            actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                        />

                        {selectedCount > 0 && (
                            <DataTableBulkActions
                                count={selectedCount}
                                onDelete={() => { void handleBulkDelete(); }}
                                isDeleting={bulkDeleteServiceCategories.isPending}
                            />
                        )}

                        <ServiceCategoriesTable
                            data={serviceCategories}
                            isPending={isPending}
                            onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.last_page > 1 && (
                            <div className="flex items-center justify-between pt-2">
                                <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                                    Page {meta.current_page} of {meta.last_page}
                                </p>
                                <div className="flex gap-2">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page <= 1}
                                        className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={16} />
                                    </button>
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page + 1)}
                                        disabled={meta.current_page >= meta.last_page}
                                        className="btn-ghost flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Next page"
                                    >
                                        <ChevronRight size={16} />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    isDeleting={deleteServiceCategory.isPending}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="service category"
                    entityName={pendingRestore?.name ?? ''}
                    isPending={restoreServiceCategory.isPending}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                />
            </AppLayout>
        </>
    );
}
