import * as React from 'react';
import { Link } from '@inertiajs/react';
import { type ColumnDef } from '@tanstack/react-table';
import { DataTable } from '@/shadcn/data-table';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import type { RoleListItem } from '@/modules/roles/types';
import { Pencil, RotateCcw, Trash2 } from 'lucide-react';

interface RolesTableProps {
  data: RoleListItem[];
  isLoading: boolean;
  isError: boolean;
  onDelete: (role: RoleListItem) => void;
  onRestore: (role: RoleListItem) => void;
}

function formatDate(value: string | null): string {
  if (!value) {
    return '—';
  }

  return new Intl.DateTimeFormat('en-US', {
    dateStyle: 'medium',
    timeStyle: 'short',
  }).format(new Date(value));
}

export default function RolesTable({
  data,
  isLoading,
  isError,
  onDelete,
  onRestore,
}: RolesTableProps): React.JSX.Element {
  const columns = React.useMemo<ColumnDef<RoleListItem>[]>(() => [
    {
      accessorKey: 'name',
      header: 'Role',
      cell: ({ row }) => (
        <div className="flex flex-col items-start gap-1">
          <span className="text-sm font-semibold text-(--text-primary)">{row.original.name}</span>
          <span className="text-xs text-(--text-muted)">{row.original.guard_name}</span>
        </div>
      ),
    },
    {
      accessorKey: 'permissions_count',
      header: 'Permissions',
      cell: ({ row }) => (
        <div className="flex flex-col items-center gap-1">
          <span className="text-sm font-semibold text-(--text-primary)">{row.original.permissions_count}</span>
          <span className="text-xs text-(--text-muted)">{row.original.permission_names.slice(0, 3).join(', ') || 'No permissions'}</span>
        </div>
      ),
    },
    {
      accessorKey: 'updated_at',
      header: 'Updated',
      cell: ({ row }) => (
        <span className="text-xs text-(--text-muted)">{formatDate(row.original.updated_at)}</span>
      ),
    },
    {
      id: 'actions',
      header: 'Actions',
      cell: ({ row }) => {
        const role = row.original;
        const isDeleted = role.deleted_at !== null;

        return (
          <div className="flex items-center justify-center gap-2">
            {!isDeleted && (
              <PermissionGuard permissions={['UPDATE_ROLE']}>
                <Link
                  href={`/roles/${role.uuid}/edit`}
                  className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-(--border-default) text-(--text-secondary) transition-all hover:bg-(--bg-hover)"
                >
                  <Pencil size={16} />
                </Link>
              </PermissionGuard>
            )}

            {!isDeleted ? (
              <PermissionGuard permissions={['DELETE_ROLE']}>
                <button
                  type="button"
                  onClick={() => onDelete(role)}
                  className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-(--border-default) text-(--accent-error) transition-all hover:bg-(--bg-hover)"
                >
                  <Trash2 size={16} />
                </button>
              </PermissionGuard>
            ) : (
              <PermissionGuard permissions={['RESTORE_ROLE']}>
                <button
                  type="button"
                  onClick={() => onRestore(role)}
                  className="inline-flex h-9 w-9 items-center justify-center rounded-xl border border-(--border-default) text-(--accent-success) transition-all hover:bg-(--bg-hover)"
                >
                  <RotateCcw size={16} />
                </button>
              </PermissionGuard>
            )}
          </div>
        );
      },
    },
  ], [onDelete, onRestore]);

  return (
    <DataTable
      columns={columns}
      data={data}
      isLoading={isLoading}
      isError={isError}
      noDataMessage="No roles found."
      errorMessage="Failed to load roles."
      loadingMessage="Loading roles..."
      getRowId={(row) => row.uuid}
    />
  );
}
