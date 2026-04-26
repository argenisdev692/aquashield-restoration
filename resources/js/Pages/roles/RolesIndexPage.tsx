import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import RolesTable from '@/pages/roles/components/RolesTable';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useRoles } from '@/modules/roles/hooks/useRoles';
import { useRoleMutations } from '@/modules/roles/hooks/useRoleMutations';
import type { RoleFilters, RoleListItem } from '@/modules/roles/types';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import { ChevronLeft, ChevronRight, Plus } from 'lucide-react';

export default function RolesIndexPage(): React.JSX.Element {
  const [filters, setFilters] = useRemember<RoleFilters>({ page: 1, per_page: 15 }, 'roles-filters');
  const [search, setSearch] = React.useState<string>(filters.search ?? '');
  const [pendingDelete, setPendingDelete] = React.useState<RoleListItem | null>(null);
  const [pendingRestore, setPendingRestore] = React.useState<RoleListItem | null>(null);

  const { data, isPending, isError } = useRoles(filters);
  const { deleteRole, restoreRole } = useRoleMutations();

  const roles = data?.data ?? [];
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

  const paginationPages = React.useMemo(() => {
    const start = Math.max(1, meta.currentPage - 2);
    const end = Math.min(meta.lastPage, start + 4);
    const adjustedStart = Math.max(1, end - 4);

    return Array.from({ length: end - adjustedStart + 1 }, (_, index) => adjustedStart + index);
  }, [meta.currentPage, meta.lastPage]);

  function goToPage(page: number): void {
    setFilters((previous) => ({ ...previous, page }));
  }

  function handleSearchChange(value: string): void {
    setSearch(value);
    setFilters((previous) => ({ ...previous, search: value || undefined, page: 1 }));
  }

  async function handleConfirmDelete(): Promise<void> {
    if (!pendingDelete) {
      return;
    }

    await deleteRole.mutateAsync(pendingDelete.uuid);
    setPendingDelete(null);
  }

  async function handleConfirmRestore(): Promise<void> {
    if (!pendingRestore) {
      return;
    }

    await restoreRole.mutateAsync(pendingRestore.uuid);
    setPendingRestore(null);
  }

  return (
    <>
      <Head title="Roles" />
      <AppLayout>
        <div className="flex flex-col gap-6 animate-in fade-in duration-300">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">Roles</h1>
              <p className="mt-1 text-sm font-medium text-(--text-muted)">
                Manage role records and recovery flow — <span className="text-(--accent-primary)">{meta.total} records</span>
              </p>
            </div>
            <PermissionGuard permissions={['CREATE_ROLE']}>
              <Link
                href="/roles/create"
                className="btn-modern btn-modern-primary flex items-center gap-2 px-4 py-2 transition-all hover:scale-[1.02] active:scale-[0.98]"
              >
                <Plus size={18} />
                <span className="font-semibold">New Role</span>
              </Link>
            </PermissionGuard>
          </div>

          <CrudFilterBar
            searchValue={search}
            onSearchChange={handleSearchChange}
            searchPlaceholder="Search roles..."
            searchAriaLabel="Search roles"
          />

          <div className="card-modern overflow-hidden border border-(--border-default) shadow-xl">
            <RolesTable
              data={roles}
              isLoading={isPending}
              isError={isError}
              onDelete={setPendingDelete}
              onRestore={setPendingRestore}
            />

            {meta.lastPage > 1 && (
              <div className="flex items-center justify-between border-t border-(--border-subtle) bg-(--bg-subtle) px-6 py-4">
                <span className="text-xs font-semibold uppercase tracking-wider text-(--text-disabled)">
                  {meta.total} {meta.total === 1 ? 'record' : 'records'} found
                </span>
                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => goToPage(meta.currentPage - 1)}
                    disabled={meta.currentPage <= 1}
                    className="flex h-9 w-9 items-center justify-center rounded-xl border border-(--border-default) bg-(--bg-card) text-(--text-muted) transition-all hover:bg-(--bg-hover) disabled:pointer-events-none disabled:opacity-30"
                  >
                    <ChevronLeft size={18} />
                  </button>
                  <div className="mx-2 flex items-center gap-1">
                    {paginationPages.map((page) => (
                      <button
                        key={page}
                        type="button"
                        onClick={() => goToPage(page)}
                        className={`h-9 w-9 rounded-xl text-xs font-bold transition-all ${
                          meta.currentPage === page
                            ? 'bg-(--accent-primary) text-(--color-white) shadow-lg'
                            : 'text-(--text-muted) hover:bg-(--bg-hover)'
                        }`}
                      >
                        {page}
                      </button>
                    ))}
                  </div>
                  <button
                    type="button"
                    onClick={() => goToPage(meta.currentPage + 1)}
                    disabled={meta.currentPage >= meta.lastPage}
                    className="flex h-9 w-9 items-center justify-center rounded-xl border border-(--border-default) bg-(--bg-card) text-(--text-muted) transition-all hover:bg-(--bg-hover) disabled:pointer-events-none disabled:opacity-30"
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
          isDeleting={deleteRole.isPending}
        />

        <RestoreConfirmModal
          isOpen={pendingRestore !== null}
          entityLabel="role"
          entityName={pendingRestore?.name ?? ''}
          onConfirm={handleConfirmRestore}
          onCancel={() => setPendingRestore(null)}
          isPending={restoreRole.isPending}
        />
      </AppLayout>
    </>
  );
}
