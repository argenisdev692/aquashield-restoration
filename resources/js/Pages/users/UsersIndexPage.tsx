import * as React from 'react';
import { Link } from '@inertiajs/react';
import { type RowSelectionState } from '@tanstack/react-table';
import AppLayout from '@/Pages/layouts/AppLayout';
import { fetchUsers } from '@/modules/users/hooks/useUsers';
import { deleteUser, bulkDeleteUsers } from '@/modules/users/hooks/useUserMutations';
import UsersTable from './components/UsersTable';
import { DataTableBulkActions } from '@/components/ui/DataTableBulkActions';
import { DeleteConfirmModal } from '@/components/ui/DeleteConfirmModal';
import { DataTableDateRangeFilter } from '@/components/common/data-table/DataTableDateRangeFilter';
import { ExportButton } from '@/components/common/export/ExportButton';
import type { UserListItem, PaginatedResponse, UserFilters } from '@/types/users';

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
// UsersIndexPage
// ══════════════════════════════════════════════════════════════
export default function UsersIndexPage(): React.JSX.Element {
  const [users, setUsers] = React.useState<UserListItem[]>([]);
  const [meta, setMeta] = React.useState<PaginatedResponse<UserListItem>['meta']>({
    currentPage: 1, lastPage: 1, perPage: 15, total: 0,
  });
  const [loading, setLoading] = React.useState<boolean>(true);
  const [isDeleting, setIsDeleting] = React.useState<boolean>(false);
  const [search, setSearch] = React.useState<string>('');
  const [filters, setFilters] = React.useState<UserFilters>({ page: 1, perPage: 15 });
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

      window.open(`/api/users/export?${params.toString()}`, '_blank');
    } catch (err) {
      console.error('Export failed', err);
    } finally {
      setIsExporting(false);
    }
  }

  // ── Fetch users ──
  const loadUsers = React.useCallback(async (f: UserFilters) => {
    setLoading(true);
    try {
      const res = await fetchUsers(f);
      setUsers(res.data);
      setMeta(res.meta);
    } catch (err) {
      console.error('Failed to fetch users', err);
    } finally {
      setLoading(false);
    }
  }, []);

  React.useEffect(() => {
    void loadUsers(filters);
  }, [filters, loadUsers]);

  // ── Search debounce ──
  function handleSearchChange(e: React.ChangeEvent<HTMLInputElement>): void {
    const value = e.target.value;
    setSearch(value);
    if (debounceRef.current) clearTimeout(debounceRef.current);
    debounceRef.current = setTimeout(() => {
      setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
    }, 400);
  }

  // ── Single Actions ──
  function handleDeleteClick(uuid: string, name: string): void {
    setPendingDelete({ uuid, name });
  }

  async function handleConfirmSingleDelete(): Promise<void> {
    if (!pendingDelete) return;
    setIsDeleting(true);
    try {
      await deleteUser(pendingDelete.uuid);
      setPendingDelete(null);
      await loadUsers(filters);
    } catch (err) {
      console.error('Failed to delete user', err);
    } finally {
      setIsDeleting(false);
    }
  }

  // ── Bulk Actions ──
  const selectedUuids = Object.keys(rowSelection).filter((k) => rowSelection[k]);

  async function handleBulkDelete(): Promise<void> {
    if (!selectedUuids.length) return;
    setIsDeleting(true);
    try {
      await bulkDeleteUsers(selectedUuids);
      setRowSelection({});
      await loadUsers(filters);
    } catch (err) {
      console.error('Failed to bulk delete users', err);
    } finally {
      setIsDeleting(false);
    }
  }

  // ── Pagination ──
  function goToPage(page: number): void {
    setFilters((prev) => ({ ...prev, page }));
  }

  const initials = (name: string, lastName: string | null): string =>
    `${name[0] ?? ''}${lastName?.[0] ?? ''}`.toUpperCase();

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
              Users
            </h1>
            <p className="text-sm mt-1" style={{ color: 'var(--text-muted)' }}>
              Manage all system users — {meta.total} total
            </p>
          </div>
          <Link
            href="/users/create"
            className="btn-modern btn-modern-primary px-4 py-2"
          >
            <IconPlus /> New User
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
              placeholder="Search users..."
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
          isDeleting={isDeleting}
        />

        {/* ── Table Card ── */}
        <div className="card-modern shadow-lg">
          <UsersTable
            data={users}
            isLoading={loading}
            onDelete={handleDeleteClick}
            initials={initials}
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
                Page {meta.currentPage} of {meta.lastPage} ({meta.total} users)
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
        isDeleting={isDeleting}
      />
    </AppLayout>
  );
}
