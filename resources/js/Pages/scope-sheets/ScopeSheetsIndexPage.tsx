import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import { motion, AnimatePresence } from 'framer-motion';
import {
    Plus, Search, Filter, Download, Trash2, X, ChevronLeft, ChevronRight,
    FileText, CalendarRange,
} from 'lucide-react';
import { type RowSelectionState } from '@tanstack/react-table';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useScopeSheets } from '@/modules/scope-sheets/hooks/useScopeSheets';
import { useDeleteScopeSheet, useRestoreScopeSheet, useBulkDeleteScopeSheets } from '@/modules/scope-sheets/hooks/useScopeSheetMutations';
import { ScopeSheetsTable } from './components/ScopeSheetsTable';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import type { ScopeSheetFilters, ScopeSheetListItem } from '@/modules/scope-sheets/types';

// ─── Sliding Paginator (5 pages around current) ───────────────────────────────

interface PaginatorProps {
    currentPage: number;
    lastPage: number;
    onPage: (p: number) => void;
}

function Paginator({ currentPage, lastPage, onPage }: PaginatorProps): React.JSX.Element {
    if (lastPage <= 1) return <></>;

    const pages: number[] = [];
    const start = Math.max(1, currentPage - 2);
    const end = Math.min(lastPage, currentPage + 2);
    for (let p = start; p <= end; p++) pages.push(p);

    return (
        <div style={{ display: 'flex', alignItems: 'center', gap: 4, fontFamily: 'var(--font-sans)' }}>
            <button type="button" onClick={() => onPage(currentPage - 1)} disabled={currentPage === 1} aria-label="Previous page" style={pageBtn(false)}>
                <ChevronLeft size={14} />
            </button>
            {start > 1 && (
                <>
                    <button type="button" onClick={() => onPage(1)} style={pageBtn(false)}>1</button>
                    {start > 2 && <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>…</span>}
                </>
            )}
            {pages.map((p) => (
                <button key={p} type="button" onClick={() => onPage(p)} style={pageBtn(p === currentPage)} aria-current={p === currentPage ? 'page' : undefined}>
                    {p}
                </button>
            ))}
            {end < lastPage && (
                <>
                    {end < lastPage - 1 && <span style={{ color: 'var(--text-disabled)', fontSize: 12 }}>…</span>}
                    <button type="button" onClick={() => onPage(lastPage)} style={pageBtn(false)}>{lastPage}</button>
                </>
            )}
            <button type="button" onClick={() => onPage(currentPage + 1)} disabled={currentPage === lastPage} aria-label="Next page" style={pageBtn(false)}>
                <ChevronRight size={14} />
            </button>
        </div>
    );
}

function pageBtn(active: boolean): React.CSSProperties {
    return {
        display: 'inline-flex', alignItems: 'center', justifyContent: 'center',
        width: 32, height: 32, borderRadius: 'var(--radius-sm)',
        border: active ? 'none' : '1px solid var(--border-default)',
        background: active ? 'var(--accent-primary)' : 'transparent',
        color: active ? 'var(--bg-base)' : 'var(--text-muted)',
        fontSize: 13, fontWeight: active ? 700 : 500,
        fontFamily: 'var(--font-sans)', cursor: 'pointer',
        transition: 'all 0.15s ease',
    };
}

// ─── Index Page ────────────────────────────────────────────────────────────────

export default function ScopeSheetsIndexPage(): React.JSX.Element {
    const [filters, setFilters] = useRemember<ScopeSheetFilters>({ page: 1, per_page: 15 }, 'scope-sheets-filters');
    const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
    const [pendingDelete, setPendingDelete] = React.useState<ScopeSheetListItem | null>(null);
    const [pendingRestore, setPendingRestore] = React.useState<ScopeSheetListItem | null>(null);
    const [showFilters, setShowFilters] = React.useState(false);
    const [, startTransition] = React.useTransition();

    const { data, isPending } = useScopeSheets(filters);
    const items = data?.data ?? [];
    const meta = data?.meta ?? { currentPage: 1, lastPage: 1, total: 0 };

    const deleteMutation = useDeleteScopeSheet();
    const restoreMutation = useRestoreScopeSheet();
    const bulkDeleteMutation = useBulkDeleteScopeSheets();

    const [optimisticItems, setOptimisticItems] = React.useOptimistic(
        items,
        (state, deletedUuid: string) => state.filter((i) => i.uuid !== deletedUuid),
    );

    const selectedUuids = Object.keys(rowSelection);

    function updateFilters(patch: Partial<ScopeSheetFilters>): void {
        startTransition(() => {
            setFilters((prev) => ({ ...prev, ...patch, page: 1 }));
        });
    }

    async function handleConfirmDelete(): Promise<void> {
        if (!pendingDelete) return;
        React.startTransition(async () => {
            setOptimisticItems(pendingDelete.uuid);
            try {
                await deleteMutation.mutateAsync(pendingDelete.uuid);
                setPendingDelete(null);
            } catch { /* auto-reverts */ }
        });
    }

    async function handleConfirmRestore(): Promise<void> {
        if (!pendingRestore) return;
        await restoreMutation.mutateAsync(pendingRestore.uuid);
        setPendingRestore(null);
    }

    async function handleBulkDelete(): Promise<void> {
        if (selectedUuids.length === 0) return;
        await bulkDeleteMutation.mutateAsync(selectedUuids);
        setRowSelection({});
    }

    function handleExport(format: 'xlsx' | 'pdf'): void {
        startTransition(() => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.status) params.append('status', filters.status);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            params.append('format', format);
            window.open(`/scope-sheets/data/admin/export?${params.toString()}`, '_blank');
        });
    }

    return (
        <>
            <Head title="Scope Sheets" />
            <AppLayout>
                <div
                    style={{
                        display: 'flex',
                        flexDirection: 'column',
                        gap: 20,
                        padding: '24px 28px',
                        maxWidth: 1200,
                        margin: '0 auto',
                        width: '100%',
                        fontFamily: 'var(--font-sans)',
                    }}
                >
                    {/* ── Header ── */}
                    <div style={{ display: 'flex', alignItems: 'flex-start', justifyContent: 'space-between', gap: 16, flexWrap: 'wrap' }}>
                        <div>
                            <h1 style={{ margin: 0, fontSize: 22, fontWeight: 800, color: 'var(--text-primary)', letterSpacing: '-0.02em' }}>
                                Scope Sheets
                            </h1>
                            <p style={{ margin: '4px 0 0', fontSize: 13, color: 'var(--text-muted)' }}>
                                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                            </p>
                        </div>
                        <div style={{ display: 'flex', alignItems: 'center', gap: 8, flexWrap: 'wrap' }}>
                            {/* Export buttons */}
                            <PermissionGuard permissions={['VIEW_SCOPE_SHEET']}>
                                <button type="button" onClick={() => handleExport('xlsx')} aria-label="Export Excel" style={ghostBtnStyle}>
                                    <Download size={14} /> Excel
                                </button>
                                <button type="button" onClick={() => handleExport('pdf')} aria-label="Export PDF" style={ghostBtnStyle}>
                                    <FileText size={14} /> PDF
                                </button>
                            </PermissionGuard>

                            {/* Filters toggle */}
                            <button
                                type="button"
                                onClick={() => setShowFilters((v) => !v)}
                                aria-label="Toggle filters"
                                style={{
                                    ...ghostBtnStyle,
                                    borderColor: showFilters ? 'var(--accent-primary)' : 'var(--border-default)',
                                    color: showFilters ? 'var(--accent-primary)' : 'var(--text-muted)',
                                }}
                            >
                                <Filter size={14} /> Filters
                            </button>

                            <PermissionGuard permissions={['CREATE_SCOPE_SHEET']}>
                                <Link
                                    href="/scope-sheets/create"
                                    style={{
                                        display: 'inline-flex', alignItems: 'center', gap: 6,
                                        padding: '8px 16px', borderRadius: 'var(--radius-md)',
                                        background: 'var(--accent-primary)', color: 'var(--bg-base)',
                                        fontSize: 13, fontWeight: 700, fontFamily: 'var(--font-sans)',
                                        textDecoration: 'none', transition: 'all 0.15s ease',
                                    }}
                                >
                                    <Plus size={15} /> New Scope Sheet
                                </Link>
                            </PermissionGuard>
                        </div>
                    </div>

                    {/* ── Filters panel ── */}
                    <AnimatePresence>
                        {showFilters && (
                            <motion.div
                                initial={{ opacity: 0, height: 0 }}
                                animate={{ opacity: 1, height: 'auto' }}
                                exit={{ opacity: 0, height: 0 }}
                                transition={{ duration: 0.22 }}
                                style={{ overflow: 'hidden' }}
                            >
                                <div
                                    style={{
                                        display: 'grid',
                                        gridTemplateColumns: 'repeat(auto-fit, minmax(200px, 1fr))',
                                        gap: 12,
                                        background: 'var(--bg-card)',
                                        border: '1px solid var(--border-default)',
                                        borderRadius: 'var(--radius-lg)',
                                        padding: '16px 20px',
                                    }}
                                >
                                    {/* Search */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                                        <label style={filterLabelStyle}>Search</label>
                                        <div style={{ position: 'relative' }}>
                                            <Search size={13} style={{ position: 'absolute', left: 10, top: '50%', transform: 'translateY(-50%)', color: 'var(--text-muted)' }} />
                                            <input
                                                type="search"
                                                value={filters.search ?? ''}
                                                onChange={(e) => updateFilters({ search: e.target.value || undefined })}
                                                placeholder="Claim number, description…"
                                                style={{ ...filterInputStyle, paddingLeft: 30 }}
                                            />
                                        </div>
                                    </div>

                                    {/* Status */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                                        <label style={filterLabelStyle}>Status</label>
                                        <select
                                            value={filters.status ?? ''}
                                            onChange={(e) => updateFilters({ status: (e.target.value as ScopeSheetFilters['status']) || undefined })}
                                            style={{ ...filterInputStyle, colorScheme: 'dark' }}
                                            aria-label="Filter by status"
                                        >
                                            <option value="">All</option>
                                            <option value="active">Active</option>
                                            <option value="deleted">Deleted</option>
                                        </select>
                                    </div>

                                    {/* Date from */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                                        <label style={filterLabelStyle}><CalendarRange size={12} /> Date From</label>
                                        <input
                                            type="date"
                                            value={filters.date_from ?? ''}
                                            onChange={(e) => updateFilters({ date_from: e.target.value || undefined })}
                                            style={{ ...filterInputStyle, colorScheme: 'dark' }}
                                            aria-label="Filter from date"
                                        />
                                    </div>

                                    {/* Date to */}
                                    <div style={{ display: 'flex', flexDirection: 'column', gap: 6 }}>
                                        <label style={filterLabelStyle}><CalendarRange size={12} /> Date To</label>
                                        <input
                                            type="date"
                                            value={filters.date_to ?? ''}
                                            onChange={(e) => updateFilters({ date_to: e.target.value || undefined })}
                                            style={{ ...filterInputStyle, colorScheme: 'dark' }}
                                            aria-label="Filter to date"
                                        />
                                    </div>

                                    {/* Clear */}
                                    <div style={{ display: 'flex', alignItems: 'flex-end' }}>
                                        <button
                                            type="button"
                                            onClick={() => setFilters({ page: 1, per_page: 15 })}
                                            aria-label="Clear all filters"
                                            style={ghostBtnStyle}
                                        >
                                            <X size={13} /> Clear filters
                                        </button>
                                    </div>
                                </div>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    {/* ── Bulk actions ── */}
                    <AnimatePresence>
                        {selectedUuids.length > 0 && (
                            <motion.div
                                initial={{ opacity: 0, y: -8 }}
                                animate={{ opacity: 1, y: 0 }}
                                exit={{ opacity: 0, y: -8 }}
                                transition={{ duration: 0.18 }}
                                style={{
                                    display: 'flex', alignItems: 'center', gap: 12,
                                    padding: '10px 16px',
                                    background: 'color-mix(in srgb, var(--accent-error) 8%, var(--bg-card))',
                                    border: '1px solid color-mix(in srgb, var(--accent-error) 25%, transparent)',
                                    borderRadius: 'var(--radius-md)',
                                    fontFamily: 'var(--font-sans)',
                                }}
                            >
                                <span style={{ fontSize: 13, fontWeight: 600, color: 'var(--text-secondary)' }}>
                                    {selectedUuids.length} selected
                                </span>
                                <PermissionGuard permissions={['DELETE_SCOPE_SHEET']}>
                                    <button
                                        type="button"
                                        onClick={handleBulkDelete}
                                        disabled={bulkDeleteMutation.isPending}
                                        aria-label={`Bulk delete ${selectedUuids.length} scope sheets`}
                                        style={{
                                            display: 'flex', alignItems: 'center', gap: 6,
                                            padding: '6px 14px', borderRadius: 'var(--radius-md)',
                                            border: '1px solid color-mix(in srgb, var(--accent-error) 40%, transparent)',
                                            background: 'color-mix(in srgb, var(--accent-error) 15%, var(--bg-card))',
                                            color: 'var(--accent-error)', fontSize: 12, fontWeight: 700,
                                            fontFamily: 'var(--font-sans)', cursor: 'pointer',
                                        }}
                                    >
                                        <Trash2 size={13} />
                                        Delete {selectedUuids.length} selected
                                    </button>
                                </PermissionGuard>
                                <button
                                    type="button"
                                    onClick={() => setRowSelection({})}
                                    aria-label="Clear selection"
                                    style={{ ...ghostBtnStyle, fontSize: 12 }}
                                >
                                    <X size={12} /> Clear
                                </button>
                            </motion.div>
                        )}
                    </AnimatePresence>

                    {/* ── Table ── */}
                    <PermissionGuard permissions={['VIEW_SCOPE_SHEET']}>
                        <ScopeSheetsTable
                            data={optimisticItems}
                            isPending={isPending}
                            rowSelection={rowSelection}
                            onRowSelectionChange={setRowSelection}
                            onDelete={setPendingDelete}
                            onRestore={setPendingRestore}
                        />
                    </PermissionGuard>

                    {/* ── Pagination ── */}
                    {meta.lastPage > 1 && (
                        <div style={{ display: 'flex', alignItems: 'center', justifyContent: 'space-between', flexWrap: 'wrap', gap: 12 }}>
                            <div style={{ display: 'flex', alignItems: 'center', gap: 8 }}>
                                <span style={{ fontSize: 12, color: 'var(--text-muted)', fontFamily: 'var(--font-sans)' }}>Rows per page:</span>
                                <select
                                    value={filters.per_page ?? 15}
                                    onChange={(e) => updateFilters({ per_page: Number(e.target.value), page: 1 })}
                                    aria-label="Rows per page"
                                    style={{ ...filterInputStyle, width: 'auto', colorScheme: 'dark', padding: '4px 8px' }}
                                >
                                    {[10, 15, 25, 50].map((n) => <option key={n} value={n}>{n}</option>)}
                                </select>
                            </div>
                            <Paginator
                                currentPage={meta.currentPage}
                                lastPage={meta.lastPage}
                                onPage={(p) => setFilters((prev) => ({ ...prev, page: p }))}
                            />
                        </div>
                    )}
                </div>

                {/* ── Delete modal ── */}
                <DeleteConfirmModal
                    open={pendingDelete !== null}
                    entityLabel={pendingDelete?.claim_number ?? pendingDelete?.claim_internal_id ?? ''}
                    onConfirm={handleConfirmDelete}
                    onCancel={() => setPendingDelete(null)}
                    isDeleting={deleteMutation.isPending}
                />

                {/* ── Restore modal ── */}
                <RestoreConfirmModal
                    isOpen={pendingRestore !== null}
                    entityLabel="scope sheet"
                    entityName={pendingRestore?.claim_number ?? pendingRestore?.claim_internal_id ?? ''}
                    onConfirm={handleConfirmRestore}
                    onCancel={() => setPendingRestore(null)}
                    isPending={restoreMutation.isPending}
                />
            </AppLayout>
        </>
    );
}

const ghostBtnStyle: React.CSSProperties = {
    display: 'inline-flex', alignItems: 'center', gap: 6,
    padding: '7px 14px', borderRadius: 'var(--radius-md)',
    border: '1px solid var(--border-default)', background: 'transparent',
    color: 'var(--text-muted)', fontSize: 13, fontWeight: 600,
    fontFamily: 'var(--font-sans)', cursor: 'pointer',
    transition: 'all 0.15s ease',
    textDecoration: 'none',
};

const filterLabelStyle: React.CSSProperties = {
    display: 'flex', alignItems: 'center', gap: 5,
    fontSize: 11, fontWeight: 700, color: 'var(--text-secondary)',
    fontFamily: 'var(--font-sans)', textTransform: 'uppercase', letterSpacing: '0.06em',
};

const filterInputStyle: React.CSSProperties = {
    width: '100%', height: 36, padding: '0 10px',
    background: 'var(--bg-elevated)', border: '1px solid var(--border-default)',
    borderRadius: 'var(--radius-md)', color: 'var(--text-primary)',
    fontSize: 13, fontFamily: 'var(--font-sans)', outline: 'none',
    boxSizing: 'border-box',
};
