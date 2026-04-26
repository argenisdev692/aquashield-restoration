import * as React from 'react';
import { Head, useRemember } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useCompanies } from '@/modules/company-data/hooks/useCompanies';
import { useCompanyDataMutations } from '@/modules/company-data/hooks/useCompanyDataMutations';
import type { CompanyDataFilters } from '@/modules/company-data/types';
import CompanyDataTable from './components/CompanyDataTable';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';

// ══════════════════════════════════════════════════════════════
// Icons
// ══════════════════════════════════════════════════════════════
const ic = {
  w: 16, h: 16, viewBox: '0 0 24 24', fill: 'none',
  stroke: 'currentColor', strokeWidth: 2,
  strokeLinecap: 'round' as const, strokeLinejoin: 'round' as const,
};
const IconChevLeft = () => <svg {...ic} width={14} height={14}><polyline points="15 18 9 12 15 6"/></svg>;
const IconChevRight = () => <svg {...ic} width={14} height={14}><polyline points="9 18 15 12 9 6"/></svg>;

// ══════════════════════════════════════════════════════════════
// CompanyDataIndexPage
// ══════════════════════════════════════════════════════════════
export default function CompanyDataIndexPage(): React.JSX.Element {
  const [filters, setFilters] = useRemember<CompanyDataFilters>({ page: 1, per_page: 15 }, 'company-filters');
  const [search, setSearch] = React.useState<string>(filters.search || '');
  const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
  
  const [isPendingExport, startExportTransition] = React.useTransition();
  const [, startSearchTransition] = React.useTransition();

  // ── Export function ──
  async function handleExport(format: 'excel' | 'pdf'): Promise<void> {
    startExportTransition(() => {
      const params = new URLSearchParams();
      if (filters.search) params.append('search', filters.search);
      if (filters.date_from) params.append('date_from', filters.date_from);
      if (filters.date_to) params.append('date_to', filters.date_to);
      params.append('format', format);

      window.open(`/company-data/data/admin/export?${params.toString()}`, '_blank');
    });
  }

  // ── Fetch data ──
  const { data, isPending, isError } = useCompanies(filters);
  const { restoreCompanyData } = useCompanyDataMutations();

  const companyList = data?.data ?? [];
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

  // ── Search change ──
  function handleSearchChange(value: string): void {
    setSearch(value);
    
    startSearchTransition(() => {
      setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
    });
  }

  function handleSingleRestore(uuid: string): void {
    const company = companyList.find((item) => item.uuid === uuid);
    setPendingRestore({ uuid, name: company?.company_name ?? uuid });
  }

  function handleConfirmSingleRestore(): void {
    if (!pendingRestore) return;
    restoreCompanyData.mutate(pendingRestore.uuid, {
      onSuccess: () => setPendingRestore(null),
    });
  }

  // ── Pagination ──
  function goToPage(page: number): void {
    setFilters((prev) => ({ ...prev, page }));
  }

  return (
    <>
      <Head title="Company Profiles" />
      <AppLayout>
      <div className="flex flex-col gap-6">
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
              {meta.total} {meta.total === 1 ? 'record' : 'records'} found
            </p>
          </div>
        </div>

        {/* ── Search bar ── */}
        <CrudFilterBar
          searchValue={search}
          onSearchChange={handleSearchChange}
          searchPlaceholder="Search companies..."
          searchAriaLabel="Search company profiles"
          dateFrom={filters.date_from}
          dateTo={filters.date_to}
          onDateRangeChange={(range) => {
            startSearchTransition(() => {
              setFilters((p) => ({
                ...p,
                date_from: range.dateFrom,
                date_to: range.dateTo,
                page: 1,
              }));
            });
          }}
          actions={(
            <PermissionGuard permissions={['VIEW_COMPANY_DATA']}>
              <ExportButton
                onExport={handleExport}
                isExporting={isPendingExport}
              />
            </PermissionGuard>
          )}
        />

        {/* ── Table Card ── */}
        <div className="card">
          <CompanyDataTable
            data={companyList}
            isLoading={isPending}
            isError={isError}
            onRestore={handleSingleRestore}
          />

          {/* ── Pagination ── */}
          {meta.lastPage > 1 && (
            <div
              className="flex items-center justify-between px-4 py-3"
              style={{ borderTop: '1px solid var(--border-subtle)' }}
            >
              <p className="text-xs" style={{ color: 'var(--text-secondary)' }}>
                Page {meta.currentPage} of {meta.lastPage} ({meta.total} entries)
              </p>
              <div className="flex items-center gap-1">
                <button
                  onClick={() => goToPage(meta.currentPage - 1)}
                  disabled={meta.currentPage <= 1}
                  aria-label="Previous page"
                  className="flex h-8 w-8 items-center justify-center rounded-lg transition-all disabled:opacity-30"
                  style={{ color: 'var(--text-muted)', border: '1px solid var(--border-default)' }}
                >
                  <IconChevLeft />
                </button>
                <button
                  onClick={() => goToPage(meta.currentPage + 1)}
                  disabled={meta.currentPage >= meta.lastPage}
                  aria-label="Next page"
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

      <RestoreConfirmModal
        isOpen={pendingRestore !== null}
        entityLabel="company"
        entityName={pendingRestore?.name ?? ''}
        onConfirm={handleConfirmSingleRestore}
        onCancel={() => setPendingRestore(null)}
        isPending={restoreCompanyData.isPending}
      />
      </AppLayout>
    </>
  );
}
