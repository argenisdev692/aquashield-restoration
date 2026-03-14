import * as React from 'react';
import { Link, Head, useRemember } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useCompanies } from '@/modules/company-data/hooks/useCompanies';
import { useCompanyDataMutations } from '@/modules/company-data/hooks/useCompanyDataMutations';
import type { CompanyDataFilters } from '@/modules/company-data/types';
import CompanyDataTable from './components/CompanyDataTable';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { DataTableDateRangeFilter } from '@/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/common/export/ExportButton';

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
  const [filters, setFilters] = useRemember<CompanyDataFilters>({ page: 1, per_page: 15 }, 'company-filters');
  const [search, setSearch] = React.useState<string>(filters.search || '');
  const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string } | null>(null);
  const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string } | null>(null);
  
  const [isPendingExport, startExportTransition] = React.useTransition();
  const [, startSearchTransition] = React.useTransition();
  const [, startFilterTransition] = React.useTransition();

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
  const { deleteCompanyData, restoreCompanyData } = useCompanyDataMutations();

  const companyList = data?.data ?? [];
  const [optimisticCompanies, removeOptimisticCompany] = React.useOptimistic(
    companyList,
    (currentCompanies, deletedUuid: string) => currentCompanies.filter((company) => company.uuid !== deletedUuid),
  );
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

  // ── Search change ──
  function handleSearchChange(e: React.ChangeEvent<HTMLInputElement>): void {
    const value = e.target.value;
    setSearch(value);
    
    startSearchTransition(() => {
      setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
    });
  }

  // ── Single Actions ──
  function handleDeleteClick(uuid: string, companyName: string): void {
    setPendingDelete({ uuid, name: companyName });
  }

  function handleConfirmSingleDelete(): void {
    if (!pendingDelete) return;
    React.startTransition(async () => {
      const deletingUuid = pendingDelete.uuid;
      removeOptimisticCompany(deletingUuid);

      try {
        await deleteCompanyData.mutateAsync(deletingUuid);
        setPendingDelete(null);
      } catch {}
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
          <PermissionGuard permissions={['CREATE_COMPANY_DATA']}>
            <Link
              href="/company-data/create"
              className="btn-primary inline-flex items-center gap-2 px-4 py-2 text-sm font-semibold"
            >
              <IconPlus /> New Company
            </Link>
          </PermissionGuard>
        </div>

        {/* ── Search bar ── */}
        <div className="card mb-4 flex flex-col items-center gap-3 px-4 py-3 sm:flex-row">
          <div className="flex flex-1 items-center gap-3 w-full">
            <span style={{ color: 'var(--text-secondary)' }}><IconSearch /></span>
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
              dateFrom={filters.date_from}
              dateTo={filters.date_to}
              onChange={(range: { dateFrom?: string; dateTo?: string }) => {
                startFilterTransition(() => {
                  setFilters((p) => ({
                    ...p,
                    date_from: range.dateFrom,
                    date_to: range.dateTo,
                    page: 1,
                  }));
                });
              }}
            />

            <div className="h-8 w-px hidden sm:block" style={{ background: 'var(--border-subtle)' }} />

            <PermissionGuard permissions={['VIEW_COMPANY_DATA']}>
              <ExportButton
                onExport={handleExport}
                isExporting={isPendingExport}
              />
            </PermissionGuard>
          </div>
        </div>

        {/* ── Table Card ── */}
        <div className="card">
          <CompanyDataTable
            data={optimisticCompanies}
            isLoading={isPending}
            isError={isError}
            onDelete={handleDeleteClick}
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

      <DeleteConfirmModal
        open={pendingDelete !== null}
        entityLabel={pendingDelete?.name ?? ''}
        onConfirm={handleConfirmSingleDelete}
        onCancel={() => setPendingDelete(null)}
        isDeleting={deleteCompanyData.isPending}
      />
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
