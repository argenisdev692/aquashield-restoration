import * as React from 'react';
import { Link } from '@inertiajs/react';
import { type RowSelectionState } from '@tanstack/react-table';
import AppLayout from '@/Pages/layouts/AppLayout';
import { useCompanyData } from '@/modules/company-data/hooks/useCompanyData';
import { useDeleteCompanyData, useRestoreCompanyData } from '@/modules/company-data/hooks/useCompanyDataMutations';
import CompanyDataTable from './components/CompanyDataTable';
import { DataTableBulkActions } from '@/components/ui/DataTableBulkActions';
import { DeleteConfirmModal } from '@/components/ui/DeleteConfirmModal';
import { DataTableDateRangeFilter } from '@/components/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/components/common/export/ExportButton';
import type { CompanyDataFilters } from '@/types/api';

// ══════════════════════════════════════════════════════════════
// Icons
// ══════════════════════════════════════════════════════════════
const ic = {
  w: 16, h: 16, viewBox: '0 0 24 24', fill: 'none',
  stroke: 'currentColor', strokeWidth: 2,
  strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const,
};
const IconPlus = () => <svg {...ic}><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>;
const IconSearch = () => <svg {...ic} width={14} height={14}><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>;
const IconChevLeft = () => <svg {...ic} width={14} height={14}><polyline points="15 18 9 12 15 6"/></svg>;
const IconChevRight = () => <svg {...ic} width={14} height={14}><polyline points="9 18 15 12 9 6"/></svg>;

// ══════════════════════════════════════════════════════════════
// CompanyDataIndexPage
// ══════════════════════════════════════════════════════════════
export default function CompanyDataIndexPage(): React.JSX.Element {
  const [filters, setFilters] = React.useState<CompanyDataFilters>({ page: 1, perPage: 15 });
  const [search, setSearch] = React.useState<string>('');
  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
  const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
  
  const debounceRef = React.useRef<ReturnType<typeof setTimeout> | null>(null);
  const [isExporting, setIsExporting] = React.useState<boolean>(false);

  // ── Export function ──
  async function handleExport(format: 'excel' | 'pdf'): Promise<void> {
    setIsExporting(true);
    try {
      const params = new URLSearchParams();
      if (filters.search) params.append('search', filters.search);
      if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
      if (filters.dateTo) params.append('dateTo', filters.dateTo);
      params.append('format', format);

      window.open(`/api/company-data/export?${params.toString()}`, '_blank');
    } catch (err) {
      console.error('Export failed', err);
    } finally {
      setIsExporting(false);
    }
  }

  // ── Fetch data ──
  const { data, isPending, isError } = useCompanyData(filters);
  const deleteMutation = useDeleteCompanyData();
  const restoreMutation = useRestoreCompanyData();

  const companyList = data?.data ?? [];
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

  // ── Search debounce ──
  function handleSearchChange(e: React.ChangeEvent<HTMLInputElement>): void {
    const value = e.target.value;
    setSearch(value);
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      // In TS strict mode, only undefined deletes it from URL query. '' gets kept.
      setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
    }, 400);
  }

  // ── Single Actions ──
  function handleDeleteClick(uuid: string, companyName: string): void {
    setPendingDelete({ uuid, name: companyName });
  }

  function handleConfirmSingleDelete(): void {
    if (!pendingDelete) return;
    deleteMutation.mutate(pendingDelete.uuid, {
      onSuccess: () => setPendingDelete(null),
    });
  }

  // ── Bulk Actions ──
  const selectedUuids = Object.keys(rowSelection).filter((k) => rowSelection[k]);
  
  function handleBulkDelete(): void {
    if (!selectedUuids.length) return;
    deleteMutation.mutate(selectedUuids, {
      onSuccess: () => setRowSelection({}),
    });
  }

  function handleBulkRestore(): void {
    if (!selectedUuids.length) return;
    restoreMutation.mutate(selectedUuids, {
      onSuccess: () => setRowSelection({}),
    });
  }

  // ── Pagination ──
  function goToPage(page: number): void {
    setFilters((prev) => ({ ...prev, page }));
  }

  return (
    <AppLayout>
      <div style={{ fontFamily: 'var(--font-sans)' }}>
        {/* ── Header ── */}
        <div className="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1
              className="text-2xl font-bold tracking-tight"
              style={{ color: 'var(--text-primary)' }}
            >
              Company Profiles
            </h1>
            <p className="text-sm mt-1" style={{ color: 'var(--text-muted)' }}>
              Manage corporate entries — {meta.total} total
            </p>
          </div>
          <Link
            href="/company-data/create"
            className="btn-modern btn-modern-primary px-4 py-2"
          >
            <IconPlus /> New Company
          </Link>
        </div>

        {/* ── Search bar ── */}
        <div
          className="mb-4 flex flex-col items-center gap-3 rounded-xl px-4 py-3 sm:flex-row"
          style={{
            background: 'var(--bg-card)',
            border: '1px solid var(--border-default)',
          }}
        >
          <div className="flex flex-1 items-center gap-3 w-full">
            <span style={{ color: 'var(--text-disabled)' }}><IconSearch /></span>
            <input
              type="text"
              value={search}
              onChange={handleSearchChange}
              placeholder="Search companies..."
              className="flex-1 bg-transparent text-sm outline-none"
              style={{
                color: 'var(--text-primary)',
                fontFamily: 'var(--font-sans)',
              }}
            />
          </div>

          <div className="flex w-full items-center gap-4 sm:w-auto">
            <div className="h-8 w-px hidden sm:block" style={{ background: 'var(--border-subtle)' }} />
            
            <DataTableDateRangeFilter
              dateFrom={filters.dateFrom || ''}
              dateTo={filters.dateTo || ''}
              onFromChange={(val) => setFilters(p => ({ ...p, dateFrom: val || undefined, page: 1 }))}
              onToChange={(val) => setFilters(p => ({ ...p, dateTo: val || undefined, page: 1 }))}
            />

            <div className="h-8 w-px hidden sm:block" style={{ background: 'var(--border-subtle)' }} />

            <ExportButton 
              onExport={handleExport} 
              isExporting={isExporting} 
            />
          </div>
        </div>

        {/* ── Bulk Actions Bar ── */}
        <DataTableBulkActions
          count={selectedUuids.length}
          onDelete={handleBulkDelete}
          onRestore={handleBulkRestore}
          isDeleting={deleteMutation.isPending}
          isRestoring={restoreMutation.isPending}
        />

        {/* ── Table Card ── */}
        <div className="card-modern shadow-lg">
          <CompanyDataTable
            data={companyList}
            isLoading={isPending}
            isError={isError}
            onDelete={handleDeleteClick}
            rowSelection={rowSelection}
            onRowSelectionChange={setRowSelection}
          />

          {/* ── Pagination ── */}
          {meta.lastPage > 1 && (
            <div
              className="flex items-center justify-between px-4 py-3"
              style={{ borderTop: '1px solid var(--border-subtle)' }}
            >
              <p className="text-xs" style={{ color: 'var(--text-disabled)' }}>
                Page {meta.currentPage} of {meta.lastPage} ({meta.total} entries)
              </p>
              <div className="flex items-center gap-1">
                <button
                  onClick={() => goToPage(meta.currentPage - 1)}
                  disabled={meta.currentPage <= 1}
                  className="flex h-8 w-8 items-center justify-center rounded-lg transition-all disabled:opacity-30"
                  style={{ color: 'var(--text-muted)', border: '1px solid var(--border-default)' }}
                >
                  <IconChevLeft />
                </button>
                <button
                  onClick={() => goToPage(meta.currentPage + 1)}
                  disabled={meta.currentPage >= meta.lastPage}
                  className="flex h-8 w-8 items-center justify-center rounded-lg transition-all disabled:opacity-30"
                  style={{ color: 'var(--text-muted)', border: '1px solid var(--border-default)' }}
                >
                  <IconChevRight />
                </button>
              </div>
            </div>
          )}
        </div>
      </div>

      <DeleteConfirmModal
        open={pendingDelete !== null}
        entityLabel={pendingDelete?.name ?? ''}
        onConfirm={handleConfirmSingleDelete}
        onCancel={() => setPendingDelete(null)}
        isDeleting={deleteMutation.isPending}
      />
    </AppLayout>
  );
}
