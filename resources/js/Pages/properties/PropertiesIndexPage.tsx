import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-react';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import {
    useBulkDeleteProperties,
    useDeleteProperty,
    useRestoreProperty,
} from '@/modules/properties/hooks/usePropertyMutations';
import { useProperties } from '@/modules/properties/hooks/useProperties';
import type { PropertyFilters } from '@/modules/properties/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PropertiesTable from './components/PropertiesTable';

function buildVisiblePages(currentPage: number, lastPage: number): number[] {
    const end = Math.min(lastPage, Math.max(5, currentPage + 2));
    const start = Math.max(1, end - 4);
    return Array.from({ length: end - start + 1 }, (_, index) => start + index);
}

export default function PropertiesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<PropertyFilters>(
        { page: 1, per_page: 15 },
        'properties-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{
        uuid: string;
        label: string;
    } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{
        uuid: string;
        label: string;
    } | null>(null);
    const [, startTransition] = React.useTransition();
    const [, startExportTransition] = React.useTransition();

    const { data, isPending, isError } = useProperties(filters);
    const deleteProperty = useDeleteProperty();
    const restoreProperty = useRestoreProperty();
    const bulkDeleteProperties = useBulkDeleteProperties();

    const properties = data?.data ?? [];
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

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams({ format });
            if (filters.search) params.append('search', filters.search);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.status) params.append('status', filters.status);
            window.open(`/properties/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deleteProperty.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreProperty.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);
        if (selectedUuids.length === 0) return;
        await bulkDeleteProperties.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Properties" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <h1
                                className="text-3xl font-extrabold"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Properties
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_PROPERTY']}>
                            <Link
                                href="/properties/create"
                                prefetch
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New property</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search by address, city, state..."
                        searchAriaLabel="Search properties"
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
                        actions={(
                            <PermissionGuard permissions={['VIEW_PROPERTY']}>
                                <ExportButton onExport={handleExport} />
                            </PermissionGuard>
                        )}
                    />

                    <PermissionGuard permissions={['DELETE_PROPERTY']}>
                        <DataTableBulkActions
                            count={selectedCount}
                            onDelete={() => { void handleBulkDelete(); }}
                            isDeleting={bulkDeleteProperties.isPending}
                        />
                    </PermissionGuard>

                    <div className="card overflow-hidden p-0">
                        <PropertiesTable
                            data={properties}
                            isPending={isPending}
                            isError={isError}
                            onDeleteClick={(uuid, label) => setPendingDelete({ uuid, label })}
                            onRestoreClick={(uuid, label) => setPendingRestore({ uuid, label })}
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
                                                          color: 'var(--color-white)',
                                                      }
                                                    : {
                                                          color: 'var(--text-secondary)',
                                                          background: 'transparent',
                                                      }
                                            }
                                            aria-label={`Go to page ${page}`}
                                            aria-current={
                                                page === meta.current_page ? 'page' : undefined
                                            }
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
                    entityLabel={pendingDelete?.label ?? ''}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteProperty.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="property"
                    entityName={pendingRestore?.label}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreProperty.isPending}
                />
            </AppLayout>
        </>
    );
}
