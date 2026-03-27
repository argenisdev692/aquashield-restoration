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
    useBulkDeleteProjectTypes,
    useDeleteProjectType,
    useRestoreProjectType,
} from '@/modules/project-types/hooks/useProjectTypeMutations';
import { useProjectTypes, useServiceCategoryOptions } from '@/modules/project-types/hooks/useProjectTypes';
import type { ProjectTypeFilters } from '@/modules/project-types/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ProjectTypesTable from './components/ProjectTypesTable';

export default function ProjectTypesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<ProjectTypeFilters>(
        { page: 1, per_page: 15 },
        'project-types-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useProjectTypes(filters);
    const { data: serviceCategories = [] } = useServiceCategoryOptions();
    const deleteProjectType = useDeleteProjectType();
    const restoreProjectType = useRestoreProjectType();
    const bulkDeleteProjectTypes = useBulkDeleteProjectTypes();

    const projectTypes = data?.data ?? [];
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
        await deleteProjectType.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreProjectType.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);

        if (selectedUuids.length === 0) return;

        await bulkDeleteProjectTypes.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.service_category_uuid) params.append('service_category_uuid', filters.service_category_uuid);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/project-types/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function goToPage(page: number): void {
        setFilters((current) => ({ ...current, page }));
    }

    return (
        <>
            <Head title="Project Types" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <h1
                                className="text-3xl font-extrabold tracking-tight"
                                style={{ color: 'var(--text-primary)' }}
                            >
                                Project Types
                            </h1>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <ExportButton onExport={handleExport} isExporting={isPendingExport} />

                        <Link
                            href="/project-types/create"
                            className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                        >
                            <Plus size={16} />
                            <span>New project type</span>
                        </Link>
                    </div>

                    <div className="card flex flex-col gap-4" style={{ fontFamily: 'var(--font-sans)' }}>
                        <div className="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                            <div
                                className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3"
                                style={{ background: 'var(--bg-surface)' }}
                            >
                                <Search size={16} style={{ color: 'var(--text-muted)' }} />
                                <input
                                    type="text"
                                    value={search}
                                    onChange={handleSearchChange}
                                    placeholder="Search project types..."
                                    className="w-full bg-transparent text-sm outline-none"
                                    style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                                />
                            </div>

                            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-end">
                                <select
                                    value={filters.service_category_uuid ?? ''}
                                    onChange={(e) =>
                                        setFilters((current) => ({
                                            ...current,
                                            service_category_uuid: e.target.value === '' ? undefined : e.target.value,
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
                                    <option value="">All categories</option>
                                    {serviceCategories.map((sc) => (
                                        <option key={sc.uuid} value={sc.uuid}>
                                            {sc.category}
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
                                isDeleting={bulkDeleteProjectTypes.isPending}
                            />
                        )}

                        <ProjectTypesTable
                            data={projectTypes}
                            isPending={isPending}
                            onDeleteClick={(uuid, title) => setPendingDelete({ uuid, name: title })}
                            onRestoreClick={(uuid, title) => setPendingRestore({ uuid, name: title })}
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
                    isDeleting={deleteProjectType.isPending}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="project type"
                    entityName={pendingRestore?.name ?? ''}
                    isPending={restoreProjectType.isPending}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                />
            </AppLayout>
        </>
    );
}
