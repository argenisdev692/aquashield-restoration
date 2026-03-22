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
    useBulkDeletePortfolios,
    useDeletePortfolio,
    useRestorePortfolio,
} from '@/modules/portfolios/hooks/usePortfolioMutations';
import { usePortfolios, useProjectTypeOptions } from '@/modules/portfolios/hooks/usePortfolios';
import type { PortfolioFilters } from '@/modules/portfolios/types';
import AppLayout from '@/pages/layouts/AppLayout';
import PortfoliosTable from './components/PortfoliosTable';

export default function PortfoliosIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<PortfolioFilters>(
        { page: 1, per_page: 15 },
        'portfolios-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = usePortfolios(filters);
    const { data: projectTypes = [] } = useProjectTypeOptions();
    const deletePortfolio = useDeletePortfolio();
    const restorePortfolio = useRestorePortfolio();
    const bulkDeletePortfolios = useBulkDeletePortfolios();

    const portfolios = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;

    function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const value = event.target.value;
        setSearch(value);
        startTransition(() => {
            setFilters((current) => ({ ...current, search: value === '' ? undefined : value, page: 1 }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deletePortfolio.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restorePortfolio.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) return;

        await bulkDeletePortfolios.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.project_type_uuid) params.append('project_type_uuid', filters.project_type_uuid);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/portfolios/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Portfolios" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Portfolios
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <ExportButton onExport={handleExport} isExporting={isPendingExport} />

                        <Link
                            href="/portfolios/create"
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New portfolio</span>
                        </Link>
                    </div>

                    <div className="card flex flex-col gap-4" style={{ fontFamily: 'var(--font-sans)' }}>
                        <div className="flex flex-col gap-3 lg:flex-row lg:items-center">
                            <div
                                className="flex flex-1 items-center gap-3 rounded-xl px-4 py-3"
                                style={{ border: '1px solid var(--border-default)', background: 'var(--bg-surface)' }}
                            >
                                <Search size={16} style={{ color: 'var(--text-muted)' }} />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={handleSearchChange}
                                    placeholder="Search by project type or category…"
                                    className="w-full bg-transparent text-sm outline-none"
                                    style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                                />
                            </div>

                            <div className="flex flex-col gap-3 sm:flex-row sm:flex-wrap sm:items-center">
                                <select
                                    value={filters.project_type_uuid ?? ''}
                                    onChange={(e) =>
                                        setFilters((current) => ({
                                            ...current,
                                            project_type_uuid: e.target.value === '' ? undefined : e.target.value,
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
                                    <option value="">All project types</option>
                                    {projectTypes.map((pt) => (
                                        <option key={pt.uuid} value={pt.uuid}>
                                            {pt.title}
                                        </option>
                                    ))}
                                </select>

                                <select
                                    value={filters.status ?? ''}
                                    onChange={(e) =>
                                        setFilters((current) => ({
                                            ...current,
                                            status: e.target.value === '' ? undefined : (e.target.value as 'active' | 'deleted'),
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
                            </div>
                        </div>

                        {selectedCount > 0 && (
                            <DataTableBulkActions
                                count={selectedCount}
                                onDelete={() => { void handleBulkDelete(); }}
                                isDeleting={bulkDeletePortfolios.isPending}
                            />
                        )}

                        <PortfoliosTable
                            data={portfolios}
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
                    isDeleting={deletePortfolio.isPending}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="portfolio"
                    entityName={pendingRestore?.name ?? ''}
                    isPending={restorePortfolio.isPending}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                />
            </AppLayout>
        </>
    );
}
