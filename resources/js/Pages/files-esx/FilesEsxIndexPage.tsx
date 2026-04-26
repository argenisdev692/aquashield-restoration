import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, FileText, Plus } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useFilesEsx } from '@/modules/files-esx/hooks/useFilesEsx';
import { useFileEsxMutations } from '@/modules/files-esx/hooks/useFileEsxMutations';
import type { FileEsx, FileEsxFilters } from '@/modules/files-esx/types';
import FilesEsxTable from './components/FilesEsxTable';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';

type OptimisticFilesEsxAction =
    | { type: 'delete'; uuid: string }
    | { type: 'bulk-delete'; uuids: string[] };

export default function FilesEsxIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<FileEsxFilters>(
        { page: 1, per_page: 15 },
        'files-esx-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startSearchTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending, isError } = useFilesEsx(filters);
    const files = data?.data ?? [];
    const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

    const [optimisticFiles, setOptimisticFiles] = React.useOptimistic<FileEsx[], OptimisticFilesEsxAction>(
        files,
        (currentState, action) => {
            if (action.type === 'delete') {
                return currentState.filter((f) => f.uuid !== action.uuid);
            }

            if (action.type === 'bulk-delete') {
                const uuids = new Set(action.uuids);

                return currentState.filter((f) => !uuids.has(f.uuid));
            }

            return currentState;
        },
    );

    const { deleteFileEsx, bulkDeleteFilesEsx } = useFileEsxMutations();

    function handleSearchChange(value: string): void {
        setSearch(value);

        startSearchTransition(() => {
            setFilters((previous) => ({ ...previous, search: value || undefined, page: 1 }));
        });
    }

    function handleDeleteClick(uuid: string, name: string): void {
        setPendingDelete({ uuid, name });
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();

            if (filters.search) params.append('search', filters.search);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);

            window.open(`/files-esx/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (!pendingDelete) return;

        const targetUuid = pendingDelete.uuid;

        React.startTransition(async () => {
            setOptimisticFiles({ type: 'delete', uuid: targetUuid });

            try {
                await deleteFileEsx.mutateAsync(targetUuid);
                setPendingDelete(null);
                setRowSelection((prev) => {
                    const next = { ...prev };
                    delete next[targetUuid];

                    return next;
                });
            } catch {
            }
        });
    }

    function goToPage(page: number): void {
        setFilters((previous) => ({ ...previous, page }));
    }

    const selectedUuids = React.useMemo(
        () => Object.keys(rowSelection).filter((key) => rowSelection[key]),
        [rowSelection],
    );

    function handleBulkDelete(): void {
        if (selectedUuids.length === 0) return;

        const uuidsToDelete = [...selectedUuids];

        React.startTransition(async () => {
            setOptimisticFiles({ type: 'bulk-delete', uuids: uuidsToDelete });

            try {
                await bulkDeleteFilesEsx.mutateAsync(uuidsToDelete);
                setRowSelection({});
            } catch {
            }
        });
    }

    const paginationPages = React.useMemo(() => {
        const start = Math.max(1, meta.currentPage - 2);
        const end = Math.min(meta.lastPage, start + 4);
        const adjustedStart = Math.max(1, end - 4);

        return Array.from({ length: end - adjustedStart + 1 }, (_, i) => adjustedStart + i);
    }, [meta.currentPage, meta.lastPage]);

    return (
        <>
            <Head title="Files ESX" />
            <AppLayout>
                <div className="flex flex-col gap-6">
                    {/* Header */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div>
                            <div className="flex items-center gap-3">
                                <div
                                    className="flex h-10 w-10 items-center justify-center rounded-xl"
                                    style={{
                                        background: 'color-mix(in srgb, var(--accent-primary) 15%, transparent)',
                                        border: '1px solid color-mix(in srgb, var(--accent-primary) 25%, transparent)',
                                    }}
                                >
                                    <FileText size={20} style={{ color: 'var(--accent-primary)' }} />
                                </div>
                                <h1
                                    className="text-3xl font-extrabold tracking-tight"
                                    style={{ color: 'var(--text-primary)' }}
                                >
                                    Files ESX
                                </h1>
                            </div>
                            <p className="mt-1 text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                Manage ESX claim files —{' '}
                                <span style={{ color: 'var(--accent-primary)' }}>
                                    {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                                </span>
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_FILES_ESX']}>
                            <Link
                                href="/files-esx/create"
                                prefetch
                                className="btn-primary flex items-center gap-2 px-4 py-2"
                            >
                                <Plus size={18} />
                                <span className="font-semibold">New File ESX</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    {/* Filters */}
                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search by file name, path or uploader..."
                        searchAriaLabel="Search files ESX"
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startSearchTransition(() => {
                                setFilters((previous) => ({
                                    ...previous,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={(
                            <PermissionGuard permissions={['VIEW_FILES_ESX']}>
                                <ExportButton onExport={handleExport} isExporting={isPendingExport} />
                            </PermissionGuard>
                        )}
                    />

                    {/* Bulk Actions */}
                    {selectedUuids.length > 0 && (
                        <PermissionGuard permissions={['DELETE_FILES_ESX']}>
                            <DataTableBulkActions
                                count={selectedUuids.length}
                                onDelete={handleBulkDelete}
                                isDeleting={bulkDeleteFilesEsx.isPending}
                            />
                        </PermissionGuard>
                    )}

                    {/* Table */}
                    <div
                        className="overflow-hidden rounded-2xl border shadow-xl"
                        style={{ borderColor: 'var(--border-default)', background: 'var(--bg-card)' }}
                    >
                        <FilesEsxTable
                            data={optimisticFiles}
                            isLoading={isPending}
                            isError={isError}
                            onDelete={handleDeleteClick}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.lastPage > 1 && (
                            <div
                                className="flex items-center justify-between border-t px-6 py-4"
                                style={{ borderColor: 'var(--border-subtle)' }}
                            >
                                <span
                                    className="text-xs font-semibold uppercase tracking-wider"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                                </span>
                                <div className="flex items-center gap-2">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.currentPage - 1)}
                                        disabled={meta.currentPage <= 1}
                                        aria-label="Previous page"
                                        className="flex h-9 w-9 items-center justify-center rounded-xl border disabled:pointer-events-none disabled:opacity-30"
                                        style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                                    >
                                        <ChevronLeft size={18} />
                                    </button>

                                    <div className="mx-2 flex items-center gap-1">
                                        {paginationPages.map((page) => (
                                            <button
                                                key={page}
                                                type="button"
                                                onClick={() => goToPage(page)}
                                                aria-label={`Go to page ${page}`}
                                                aria-current={meta.currentPage === page ? 'page' : undefined}
                                                className="h-9 w-9 rounded-xl text-xs font-bold"
                                                style={
                                                    meta.currentPage === page
                                                        ? { background: 'var(--accent-primary)', color: 'var(--text-primary)' }
                                                        : { color: 'var(--text-muted)' }
                                                }
                                            >
                                                {page}
                                            </button>
                                        ))}
                                    </div>

                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.currentPage + 1)}
                                        disabled={meta.currentPage >= meta.lastPage}
                                        aria-label="Next page"
                                        className="flex h-9 w-9 items-center justify-center rounded-xl border disabled:pointer-events-none disabled:opacity-30"
                                        style={{ borderColor: 'var(--border-default)', color: 'var(--text-muted)' }}
                                    >
                                        <ChevronRight size={18} />
                                    </button>
                                </div>
                            </div>
                        )}
                    </div>
                </div>

                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteFileEsx.isPending}
                />
            </AppLayout>
        </>
    );
}
