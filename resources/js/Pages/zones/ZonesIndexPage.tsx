import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, MapPin, Plus, Search } from 'lucide-react';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/common/export/ExportButton';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useZones } from '@/modules/zones/hooks/useZones';
import {
    useBulkDeleteZones,
    useDeleteZone,
    useRestoreZone,
} from '@/modules/zones/hooks/useZoneMutations';
import type { ZoneFilters, ZoneListItem, ZoneType } from '@/modules/zones/types';
import { ZONE_TYPE_LABELS } from '@/modules/zones/types';
import AppLayout from '@/pages/layouts/AppLayout';
import ZonesTable from './components/ZonesTable';

const ZONE_TYPES: ZoneType[] = ['interior', 'exterior', 'basement', 'attic', 'garage', 'crawlspace'];

function getSlidingPages(current: number, last: number): number[] {
    const delta = 2;
    const from = Math.max(1, current - delta);
    const to = Math.min(last, current + delta);
    const pages: number[] = [];
    for (let i = from; i <= to; i++) pages.push(i);
    return pages;
}

export default function ZonesIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<ZoneFilters>(
        { page: 1, per_page: 15 },
        'zones-filters',
    );
    const [search, setSearch] = React.useState<string>(filters.search ?? '');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
    const [, startTransition] = React.useTransition();
    const [isPendingExport, startExportTransition] = React.useTransition();

    const { data, isPending } = useZones(filters);
    const deleteZone = useDeleteZone();
    const restoreZone = useRestoreZone();
    const bulkDeleteZones = useBulkDeleteZones();

    const zones = data?.data ?? [];
    const meta = data?.meta ?? { current_page: 1, last_page: 1, per_page: 15, total: 0 };
    const selectedCount = Object.values(rowSelection).filter(Boolean).length;

    const [optimisticZones, setOptimisticZones] = React.useOptimistic(
        zones,
        (state: ZoneListItem[], deletedUuid: string) =>
            state.filter((z) => z.uuid !== deletedUuid),
    );

    function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
        const value = event.target.value;
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
        React.startTransition(async () => {
            setOptimisticZones(pendingDelete.uuid);
            try {
                await deleteZone.mutateAsync(pendingDelete.uuid);
                setPendingDelete(null);
            } catch {
                /* reverts automatically */
            }
        });
    }

    async function handleConfirmRestore(): Promise<void> {
        if (pendingRestore === null) return;
        await restoreZone.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        const selectedUuids = Object.entries(rowSelection)
            .filter(([, selected]) => selected)
            .map(([uuid]) => uuid);
        if (selectedUuids.length === 0) return;
        await bulkDeleteZones.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'excel' | 'pdf'): void {
        startExportTransition(() => {
            const params = new URLSearchParams();
            if (filters.search)    params.append('search',    filters.search);
            if (filters.zone_type) params.append('zone_type', filters.zone_type);
            if (filters.status)    params.append('status',    filters.status);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to)   params.append('date_to',   filters.date_to);
            params.append('format', format);
            window.open(`/zones/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    function goToPage(page: number): void {
        setFilters((prev) => ({ ...prev, page }));
    }

    const slidingPages = getSlidingPages(meta.current_page, meta.last_page);

    return (
        <>
            <Head title="Zones" />
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
                                    <MapPin size={20} />
                                </div>
                                <h1
                                    className="text-3xl font-extrabold tracking-tight"
                                    style={{ color: 'var(--text-primary)', letterSpacing: '-0.5px' }}
                                >
                                    Zones
                                </h1>
                            </div>
                            <p className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>

                        <PermissionGuard permissions={['CREATE_ZONE']}>
                            <Link
                                href="/zones/create"
                                className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
                            >
                                <Plus size={16} />
                                <span>New zone</span>
                            </Link>
                        </PermissionGuard>
                    </div>

                    {/* ── Toolbar / Filters ── */}
                    <div
                        className="flex flex-col gap-4 rounded-3xl px-5 py-4 shadow-sm lg:flex-row lg:items-end lg:justify-between"
                        style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', fontFamily: 'var(--font-sans)' }}
                    >
                        <div
                            className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3"
                            style={{ background: 'var(--bg-surface)' }}
                        >
                            <Search size={16} style={{ color: 'var(--text-muted)', flexShrink: 0 }} />
                            <input
                                type="text"
                                value={search}
                                onChange={handleSearchChange}
                                placeholder="Search zones by name, code or description…"
                                className="w-full bg-transparent text-sm outline-none"
                                style={{ color: 'var(--text-primary)', fontFamily: 'var(--font-sans)' }}
                            />
                        </div>

                        <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-end">
                            <select
                                value={filters.status ?? ''}
                                onChange={(e) =>
                                    setFilters((prev) => ({
                                        ...prev,
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
                                    colorScheme: 'dark',
                                }}
                            >
                                <option value="">All status</option>
                                <option value="active">Active</option>
                                <option value="deleted">Deleted</option>
                            </select>

                            <select
                                value={filters.zone_type ?? ''}
                                onChange={(e) =>
                                    setFilters((prev) => ({
                                        ...prev,
                                        zone_type: e.target.value === '' ? undefined : (e.target.value as ZoneType),
                                        page: 1,
                                    }))
                                }
                                className="rounded-xl px-4 py-3 text-sm outline-none"
                                style={{
                                    border: '1px solid var(--border-default)',
                                    background: 'var(--bg-surface)',
                                    color: 'var(--text-primary)',
                                    fontFamily: 'var(--font-sans)',
                                    colorScheme: 'dark',
                                }}
                            >
                                <option value="">All types</option>
                                {ZONE_TYPES.map((type) => (
                                    <option key={type} value={type}>
                                        {ZONE_TYPE_LABELS[type]}
                                    </option>
                                ))}
                            </select>

                            <DataTableDateRangeFilter
                                dateFrom={filters.date_from}
                                dateTo={filters.date_to}
                                onChange={(range) =>
                                    setFilters((prev) => ({
                                        ...prev,
                                        date_from: range.dateFrom,
                                        date_to: range.dateTo,
                                        page: 1,
                                    }))
                                }
                            />

                            <ExportButton onExport={handleExport} isExporting={isPendingExport} />
                        </div>
                    </div>

                    {/* ── Bulk Actions ── */}
                    <DataTableBulkActions
                        count={selectedCount}
                        onDelete={() => { void handleBulkDelete(); }}
                        isDeleting={bulkDeleteZones.isPending}
                    />

                    {/* ── Table + Paginator ── */}
                    <div className="card overflow-hidden p-0">
                        <ZonesTable
                            data={optimisticZones}
                            isPending={isPending}
                            onDeleteClick={(uuid, name) => setPendingDelete({ uuid, name })}
                            onRestoreClick={(uuid, name) => setPendingRestore({ uuid, name })}
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
                                    {/* Prev */}
                                    <button
                                        type="button"
                                        onClick={() => goToPage(meta.current_page - 1)}
                                        disabled={meta.current_page === 1}
                                        className="btn-ghost inline-flex h-9 w-9 items-center justify-center rounded-lg p-0 disabled:opacity-40"
                                        aria-label="Previous page"
                                    >
                                        <ChevronLeft size={15} />
                                    </button>

                                    {/* First page gap */}
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

                                    {/* Sliding pages */}
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

                                    {/* Last page gap */}
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

                                    {/* Next */}
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

                {/* ── Modals ── */}
                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.name ?? ''}
                    onConfirm={() => { void handleConfirmDelete(); }}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteZone.isPending}
                />

                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="zone"
                    entityName={pendingRestore?.name}
                    onConfirm={() => { void handleConfirmRestore(); }}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreZone.isPending}
                />
            </AppLayout>
        </>
    );
}
