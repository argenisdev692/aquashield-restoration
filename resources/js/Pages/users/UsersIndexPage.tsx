import * as React from 'react';
import { Link, Head, useRemember } from '@inertiajs/react';
import { type RowSelectionState } from '@tanstack/react-table';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import AppLayout from '@/pages/layouts/AppLayout';
import { useUsers } from '@/modules/users/hooks/useUsers';
import { useUserMutations } from '@/modules/users/hooks/useUserMutations';
import UsersTable from './components/UsersTable';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import type { UserFilters, UserListItem } from '@/modules/users/types';
import { ChevronLeft, ChevronRight, UserPlus } from 'lucide-react';

type OptimisticUsersAction =
  | { type: 'delete'; uuid: string; removeFromList: boolean }
  | { type: 'bulk-delete'; uuids: string[]; removeFromList: boolean }
  | { type: 'restore'; uuid: string; removeFromList: boolean };

/**
 * UsersIndexPage — Super-admin management for users.
 */
export default function UsersIndexPage(): React.JSX.Element {
  const [filters, setFilters] = useRemember<UserFilters>({ page: 1, per_page: 15 }, 'users-filters');
  const [search, setSearch] = React.useState<string>(filters.search || '');
  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
  const [pendingDelete, setPendingDelete] = React.useState<{ uuid: string; name: string; email: string } | null>(null);
  const [pendingRestore, setPendingRestore] = React.useState<{ uuid: string; name: string; email: string } | null>(null);
  
  const [isPendingExport, startExportTransition] = React.useTransition();
  const [, startSearchTransition] = React.useTransition();

  // ── Fetch users via TanStack Query ──
  const { data, isPending, isError } = useUsers(filters);
  const users = data?.data ?? [];
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };
  const isActiveFilter = filters.status === 'active';
  const isDeletedFilter = filters.status === 'deleted';
  const [optimisticUsers, setOptimisticUsers] = React.useOptimistic<UserListItem[], OptimisticUsersAction>(
    users,
    (currentState, action) => {
      if (action.type === 'delete') {
        if (action.removeFromList) {
          return currentState.filter((user) => user.uuid !== action.uuid);
        }

        return currentState.map((user) =>
          user.uuid === action.uuid
            ? { ...user, status: 'deleted', deleted_at: new Date().toISOString() }
            : user,
        );
      }

      if (action.type === 'bulk-delete') {
        const uuids = new Set(action.uuids);

        if (action.removeFromList) {
          return currentState.filter((user) => !uuids.has(user.uuid));
        }

        return currentState.map((user) =>
          uuids.has(user.uuid)
            ? { ...user, status: 'deleted', deleted_at: new Date().toISOString() }
            : user,
        );
      }

      if (action.removeFromList) {
        return currentState.filter((user) => user.uuid !== action.uuid);
      }

      return currentState.map((user) => {
        if (user.uuid !== action.uuid) {
          return user;
        }

        return {
          ...user,
          status: 'active',
          deleted_at: null,
        };
      });
    },
  );

  const { deleteUser, restoreUser, bulkDeleteUsers } = useUserMutations();

  // ── Export function ──
  async function handleExport(format: 'excel' | 'pdf'): Promise<void> {
    startExportTransition(() => {
      const params = new URLSearchParams();
      if (filters.search) params.append('search', filters.search);
      if (filters.date_from) params.append('date_from', filters.date_from);
      if (filters.date_to) params.append('date_to', filters.date_to);
      if (filters.status) params.append('status', filters.status);
      params.append('format', format);

      window.open(`/users/data/admin/export?${params.toString()}`, '_blank');
    });
  }

  // ── Search Change ──
  function handleSearchChange(value: string): void {
    setSearch(value);
    
    startSearchTransition(() => {
      setFilters((prev) => ({ ...prev, search: value || undefined, page: 1 }));
    });
  }

  // ── Single Actions ──
  function handleDeleteClick(uuid: string, name: string, email: string): void {
    setPendingDelete({ uuid, name, email });
  }

  function handleRestoreClick(uuid: string, name: string, email: string): void {
    setPendingRestore({ uuid, name, email });
  }

  async function handleConfirmSingleDelete(): Promise<void> {
    if (!pendingDelete) return;

    const targetUuid = pendingDelete.uuid;

    React.startTransition(async () => {
      setOptimisticUsers({ type: 'delete', uuid: targetUuid, removeFromList: isActiveFilter });

      try {
        await deleteUser.mutateAsync(targetUuid);
        setPendingDelete(null);
        setRowSelection((currentSelection) => {
          const nextSelection = { ...currentSelection };
          delete nextSelection[targetUuid];

          return nextSelection;
        });
      } catch {
      }
    });
  }

  async function handleConfirmRestore(): Promise<void> {
    if (!pendingRestore) return;

    const targetUuid = pendingRestore.uuid;

    React.startTransition(async () => {
      setOptimisticUsers({ type: 'restore', uuid: targetUuid, removeFromList: isDeletedFilter });

      try {
        await restoreUser.mutateAsync(targetUuid);
        setPendingRestore(null);
        setRowSelection((currentSelection) => {
          const nextSelection = { ...currentSelection };
          delete nextSelection[targetUuid];

          return nextSelection;
        });
      } catch {
      }
    });
  }

  function handleBulkDelete(): void {
    if (selectedActiveUuids.length === 0) {
      return;
    }

    const uuidsToDelete = [...selectedActiveUuids];

    React.startTransition(async () => {
      setOptimisticUsers({ type: 'bulk-delete', uuids: uuidsToDelete, removeFromList: isActiveFilter });

      try {
        await bulkDeleteUsers.mutateAsync(uuidsToDelete);
        setRowSelection({});
      } catch {
      }
    });
  }

  // ── Pagination ──
  function goToPage(page: number): void {
    setFilters((prev) => ({ ...prev, page }));
  }

  const initials = React.useCallback((name: string, lastName: string): string => {
    if (!name && !lastName) return 'U';
    const f = (name || '').trim().charAt(0).toUpperCase();
    const l = (lastName || '').trim().charAt(0).toUpperCase();
    return f && l ? f + l : f || l || 'U';
  }, []);

  const selectedUuids = React.useMemo(() => 
    Object.keys(rowSelection).filter((k) => rowSelection[k]),
    [rowSelection]
  );

  const selectedActiveUuids = React.useMemo(
    () => optimisticUsers
      .filter((user) => selectedUuids.includes(user.uuid) && !user.deleted_at)
      .map((user) => user.uuid),
    [optimisticUsers, selectedUuids]
  );

  function mapStatusValue(value: string): UserFilters['status'] | undefined {
    return value === 'all' ? undefined : value as UserFilters['status'];
  }

  const paginationPages = React.useMemo(() => {
    const start = Math.max(1, meta.currentPage - 2);
    const end = Math.min(meta.lastPage, start + 4);
    const adjustedStart = Math.max(1, end - 4);

    return Array.from({ length: end - adjustedStart + 1 }, (_, index) => adjustedStart + index);
  }, [meta.currentPage, meta.lastPage]);

  return (
    <>
      <Head title="System Users" />
      <AppLayout>
      <div className="flex flex-col gap-6 animate-in fade-in duration-300">
        {/* ── Header ── */}
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">
              System Users
            </h1>
            <p className="text-sm mt-1 text-(--text-muted) font-medium">
              Oversee and manage platform accounts — <span className="text-(--accent-primary)">{meta.total} {meta.total === 1 ? 'record' : 'records'} found</span>
            </p>
          </div>
          <PermissionGuard permissions={['CREATE_USERS']}>
            <Link
              href="/users/create"
              prefetch
              className="btn-modern btn-modern-primary flex items-center gap-2 px-4 py-2 hover:scale-[1.02] active:scale-[0.98] transition-all"
            >
              <UserPlus size={18} />
              <span className="font-semibold">New User</span>
            </Link>
          </PermissionGuard>
        </div>

        {/* ── Filters Bar ── */}
        <CrudFilterBar
          searchValue={search}
          onSearchChange={handleSearchChange}
          searchPlaceholder="Filter by name, email or identity..."
          searchAriaLabel="Search users"
          statusValue={filters.status ?? ''}
          onStatusChange={(value) => {
            startSearchTransition(() => {
              setFilters((p) => ({
                ...p,
                status: mapStatusValue(value === '' ? 'all' : value),
                page: 1,
              }));
            });
          }}
          dateFrom={filters.date_from}
          dateTo={filters.date_to}
          onDateRangeChange={(range) => {
            startSearchTransition(() => {
              setFilters(p => ({ 
                ...p, 
                date_from: range.dateFrom, 
                date_to: range.dateTo, 
                page: 1 
              }));
            });
          }}
          actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
        />

        {/* ── Bulk Actions Bar ── */}
        {selectedActiveUuids.length > 0 && (
            <PermissionGuard permissions={['DELETE_USERS']}>
              <DataTableBulkActions
                  count={selectedActiveUuids.length}
                  onDelete={handleBulkDelete}
                  isDeleting={bulkDeleteUsers.isPending}
              />
            </PermissionGuard>
        )}

        {/* ── Table Card ── */}
        <div className="card-modern overflow-hidden border border-(--border-default) shadow-xl">
          <UsersTable
            data={optimisticUsers}
            isLoading={isPending}
            isError={isError}
            onDelete={handleDeleteClick}
            onRestore={handleRestoreClick}
            initials={initials}
            rowSelection={rowSelection}
            onRowSelectionChange={setRowSelection}
          />

          {/* ── Pagination ── */}
          {meta.lastPage > 1 && (
            <div className="flex items-center justify-between px-6 py-4 border-t border-(--border-subtle) bg-(--bg-subtle)">
              <span className="text-xs font-semibold text-(--text-disabled) uppercase tracking-wider">
                {meta.total} {meta.total === 1 ? 'record' : 'records'} found
              </span>
              <div className="flex items-center gap-2">
                <button
                  onClick={() => goToPage(meta.currentPage - 1)}
                  disabled={meta.currentPage <= 1}
                  className="flex h-9 w-9 items-center justify-center rounded-xl bg-(--bg-card) border border-(--border-default) text-(--text-muted) hover:bg-(--bg-hover) disabled:opacity-30 disabled:pointer-events-none transition-all"
                >
                  <ChevronLeft size={18} />
                </button>
                <div className="flex items-center gap-1 mx-2">
                    {paginationPages.map((p) => {
                        return (
                            <button
                                key={p}
                                onClick={() => goToPage(p)}
                                className={`h-9 w-9 rounded-xl text-xs font-bold transition-all ${
                                    meta.currentPage === p 
                                    ? 'bg-(--accent-primary) text-(--color-white) shadow-lg' 
                                    : 'hover:bg-(--bg-hover) text-(--text-muted)'
                                }`}
                            >
                                {p}
                            </button>
                        );
                    })}
                </div>
                <button
                  onClick={() => goToPage(meta.currentPage + 1)}
                  disabled={meta.currentPage >= meta.lastPage}
                  className="flex h-9 w-9 items-center justify-center rounded-xl bg-(--bg-card) border border-(--border-default) text-(--text-muted) hover:bg-(--bg-hover) disabled:opacity-30 disabled:pointer-events-none transition-all"
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
        entityLabel={pendingDelete ? `${pendingDelete.name} (${pendingDelete.email})` : ''}
        onConfirm={handleConfirmSingleDelete}
        onCancel={() => setPendingDelete(null)}
        isDeleting={deleteUser.isPending}
      />
      <RestoreConfirmModal
        isOpen={pendingRestore !== null}
        entityLabel="user"
        entityName={pendingRestore ? `${pendingRestore.name} (${pendingRestore.email})` : ''}
        onConfirm={handleConfirmRestore}
        onCancel={() => setPendingRestore(null)}
        isPending={restoreUser.isPending}
      />
      </AppLayout>
    </>
  );
}
