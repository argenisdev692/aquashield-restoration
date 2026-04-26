import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, FileText, Plus } from 'lucide-react';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useDocumentTemplateAdjusters } from '@/modules/document-template-adjusters/hooks/useDocumentTemplateAdjusters';
import {
    useBulkDeleteDocumentTemplateAdjusters,
    useDeleteDocumentTemplateAdjuster,
} from '@/modules/document-template-adjusters/hooks/useDocumentTemplateAdjusterMutations';
import { ADJUSTER_TEMPLATE_TYPES } from '@/modules/document-template-adjusters/types';
import type { DocumentTemplateAdjusterFilters } from '@/modules/document-template-adjusters/types';
import AppLayout from '@/pages/layouts/AppLayout';
import DocumentTemplateAdjustersTable from './components/DocumentTemplateAdjustersTable';

function getSlidingPages(current: number, last: number): number[] {
    const delta = 2;
    const from = Math.max(1, current - delta);
    const to = Math.min(last, current + delta);
    const pages: number[] = [];
    for (let i = from; i <= to; i++) pages.push(i);
    return pages;
}

export default function DocumentTemplateAdjusterIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<DocumentTemplateAdjusterFilters>(
        { page: 1, per_page: 15 },
        'document-template-adjusters-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; label: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useDocumentTemplateAdjusters(filters);
    const deleteAdjuster = useDeleteDocumentTemplateAdjuster();
    const bulkDelete = useBulkDeleteDocumentTemplateAdjusters();

    const items = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;
    const slidingPages = getSlidingPages(meta.current_page, meta.last_page);

    function handleSearchChange(value: string): void {
        setSearch(value);
        startTransition(() => {
            setFilters((prev) => ({
                ...prev,
                search: value === '' ? undefined : value,
                page: 1,
            }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (pendingDelete === null) return;
        await deleteAdjuster.mutateAsync(pendingDelete.uuid);
        setPendingDelete(null);
        setRowSelection({});
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);
        if (selectedUuids.length === 0) return;
        await bulkDelete.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.public_adjuster_id) params.append('public_adjuster_id', String(filters.public_adjuster_id));
            if (filters.template_type_adjuster) params.append('template_type_adjuster', filters.template_type_adjuster);
            params.append('format', format);
            window.open(`/document-template-adjusters/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function goToPage(page: number): void {
        setFilters((prev) => ({ ...prev, page }));
    }

    return (
        <>
            <Head title="Document Template Adjusters" />
            <AppLayout>
                <div className="flex flex-col gap-6">

                    {/* ── Header ── */}
                    <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
                        <div className="space-y-1">
                            <div className="flex items-center gap-3">
                                <div
                                    className="flex h-10 w-10 items-center justify-center rounded-xl"
                                    style={{
                                        background: 'color-mix(in srgb, var(--accent-primary) 12%, transparent)',
                                        color: 'var(--accent-primary)',
                                    }}
                                >
                                    <FileText size={20} />
                                </div>
                                <h1
                                    className="text-3xl font-extrabold tracking-tight"
                                    style={{ color: 'var(--text-primary)', letterSpacing: '-0.5px' }}
                                >
                                    Document Template Adjusters
                                </h1>
                            </div>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_DOCUMENT_TEMPLATE_ADJUSTER']}>
                            <Link
                                href="/document-template-adjusters/create"
                                prefetch
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New Template</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    {/* ── Toolbar / Filters ── */}
                    <CrudFilterBar
                        searchValue={search}
                        onSearchChange={handleSearchChange}
                        searchPlaceholder="Search by description or type…"
                        searchAriaLabel="Search document template adjusters"
                        selects={[
                            {
                                value: filters.template_type_adjuster ?? '',
                                onChange: (value) => {
                                    startTransition(() => {
                                        setFilters((prev) => ({
                                            ...prev,
                                            template_type_adjuster: value === '' ? undefined : value,
                                            page: 1,
                                        }));
                                    });
                                },
                                options: [{ value: '', label: 'All Types' }, ...ADJUSTER_TEMPLATE_TYPES],
                                ariaLabel: 'Filter by template type',
                                label: 'Type',
                                minWidth: 160,
                            },
                        ]}
                        dateFrom={filters.date_from}
                        dateTo={filters.date_to}
                        onDateRangeChange={(range) => {
                            startTransition(() => {
                                setFilters((prev) => ({
                                    ...prev,
                                    date_from: range.dateFrom,
                                    date_to: range.dateTo,
                                    page: 1,
                                }));
                            });
                        }}
                        actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
                    />

                    {/* ── Bulk Actions ── */}
                    <DataTableBulkActions
                        count={selectedCount}
                        onDelete={() => { void handleBulkDelete(); }}
                        isDeleting={bulkDelete.isPending}
                    />

                    {/* ── Table + Paginator ── */}
                    <div className="card overflow-hidden p-0">
                        <DocumentTemplateAdjustersTable
                            data={items}
                            isPending={isPending}
                            onDeleteClick={(uuid, label) => setPendingDelete({ uuid, label })}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                        />

                        {meta.last_page > 1 ? (
                            <div
                                className="flex items-center justify-between gap-4 px-6 py-4"
                                style={{ borderTop: '1px solid var(--border-subtle)' }}
                            >
                                <span
                                    className="text-xs font-semibold uppercase tracking-[1.5px]"
                                    style={{ color: 'var(--text-disabled)' }}
                                >
                                    Page {meta.current_page} / {meta.last_page}
                                </span>

                                <div className="flex items-center gap-1">
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page === 1}
                                        className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={15} />
                                    </button>

                                    {slidingPages[0] > 1 ? (
                                        <>
                                            <button
                                                type="button"
                                                onClick={() => goToPage(1)}
                                                className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 text-xs font-semibold"
                                                aria-label="Go to page 1"
                                            >
                                                1
                                            </button>
                                            {slidingPages[0] > 2 ? (
                                                <span className="px-1 text-xs" style={{ color: 'var(--text-disabled)' }}>…</span>
                                            ) : null}
                                        </>
                                    ) : null}

                                    {slidingPages.map((page) => (
                                        <button
                                            key={page}
                                            type="button"
                                            onClick={() => goToPage(page)}
                                            className="inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 text-xs font-semibold transition-all"
                                            style={{
                                                background: page === meta.current_page
                                                    ? 'var(--accent-primary)'
                                                    : 'transparent',
                                                color: page === meta.current_page
                                                    ? 'var(--text-primary)'
                                                    : 'var(--text-muted)',
                                                border: page === meta.current_page
                                                    ? 'none'
                                                    : '1px solid var(--border-default)',
                                            }}
                                            aria-label={`Go to page ${page}`}
                                            aria-current={page === meta.current_page ? 'page' : undefined}
                                        >
                                            {page}
                                        </button>
                                    ))}

                                    {slidingPages[slidingPages.length - 1] < meta.last_page ? (
                                        <>
                                            {slidingPages[slidingPages.length - 1] < meta.last_page - 1 ? (
                                                <span className="px-1 text-xs" style={{ color: 'var(--text-disabled)' }}>…</span>
                                            ) : null}
                                            <button
                                                type="button"
                                                onClick={() => goToPage(meta.last_page)}
                                                className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 text-xs font-semibold"
                                                aria-label={`Go to page ${meta.last_page}`}
                                            >
                                                {meta.last_page}
                                            </button>
                                        </>
                                    ) : null}

                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page + 1)}
                                        disabled={meta.current_page === meta.last_page}
                                        className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Next page"
                                    >
                                        <ChevronRight size={15} />
                                    </button>
                                </div>
                            </div>
                        ) : null}
                    </div>
                </div>

                {/* ── Delete Modal ── */}
                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.label ?? ''}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteAdjuster.isPending}
                />
            </AppLayout>
        </>
    );
}
