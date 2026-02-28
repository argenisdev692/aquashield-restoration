import * as React from 'react';
import { createColumnHelper, type ColumnDef, type RowSelectionState, type OnChangeFn } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { DataTable } from '@/components/ui/data-table';
import UserStatusBadge from '@/modules/users/components/UserStatusBadge';
import type { UserListItem } from '@/types/users';

import { Eye, Pencil, Trash2 } from 'lucide-react';

// ══════════════════════════════════════════════════════════════
// Props
// ══════════════════════════════════════════════════════════════
interface UsersTableProps {
  data: UserListItem[];
  isLoading: boolean;
  isError?: boolean;
  onDelete: (uuid: string, name: string) => void;
  initials: (name: string, lastName: string | null) => string;
  rowSelection: RowSelectionState;
  onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

export default function UsersTable({
  data,
  isLoading,
  isError = false,
  onDelete,
  initials,
  rowSelection,
  onRowSelectionChange,
}: UsersTableProps) {
  const columnHelper = createColumnHelper<UserListItem>();

  const columns = React.useMemo<ColumnDef<UserListItem, any>[]>(() => [
    columnHelper.display({
      id: 'select',
      header: ({ table }) => (
        <input
          type="checkbox"
          checked={table.getIsAllPageRowsSelected()}
          onChange={table.getToggleAllPageRowsSelectedHandler()}
          aria-label="Select all"
          className="h-4 w-4 rounded border-gray-300 accent-(--accent-primary) cursor-pointer"
        />
      ),
      cell: ({ row }) => (
        <input
          type="checkbox"
          checked={row.getIsSelected()}
          onChange={row.getToggleSelectedHandler()}
          aria-label="Select row"
          className="h-4 w-4 rounded border-gray-300 accent-(--accent-primary) cursor-pointer"
        />
      ),
    }),
    columnHelper.accessor('full_name', {
      header: 'User',
      cell: (info) => {
        const user = info.row.original;
        return (
          <div className="flex items-center justify-center gap-3 text-left">
            {user.profile_photo_path ? (
              <img
                src={user.profile_photo_path}
                alt={user.full_name}
                className="h-9 w-9 rounded-lg object-cover"
              />
            ) : (
              <div
                className="flex h-9 w-9 items-center justify-center rounded-lg text-[11px] font-bold"
                style={{
                  background: 'var(--grad-primary)',
                  color: '#ffffff',
                }}
              >
                {initials(user.name, user.last_name)}
              </div>
            )}
            <div>
              <p className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
                {user.full_name}
              </p>
              {user.username && (
                <p className="text-[11px]" style={{ color: 'var(--text-disabled)' }}>
                  @{user.username}
                </p>
              )}
            </div>
          </div>
        );
      },
    }),
    columnHelper.accessor('email', {
      header: 'Email',
      cell: (info) => <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>{info.getValue() ?? '—'}</span>,
    }),
    columnHelper.accessor('status', {
      header: 'Status',
      cell: (info) => <UserStatusBadge status={info.getValue()} />,
    }),
    columnHelper.accessor('created_at', {
      header: 'Created',
      cell: (info) => {
        const val = info.getValue() as string | undefined;
        if (!val) return '—';
        try {
          const date = new Date(val);
          if (isNaN(date.getTime())) return '—';
          return (
            <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
              {date.toLocaleDateString()}
            </span>
          );
        } catch (e) {
          return '—';
        }
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: 'Actions',
      cell: (info) => {
        const user = info.row.original;
        return (
          <div className="flex items-center justify-center gap-2">
            <Link
               href={`/users/${user.uuid}`}
               className="btn-action btn-action-view"
               title="View"
            >
               <Eye size={14} />
            </Link>
            <Link
               href={`/users/${user.uuid}/edit`}
               className="btn-action btn-action-edit"
               title="Edit"
            >
               <Pencil size={14} />
            </Link>
            <button
               onClick={() => onDelete(user.uuid, user.full_name)}
               className="btn-action btn-action-delete"
               title="Delete"
            >
               <Trash2 size={14} />
            </button>
          </div>
        );
      },
    }),
  ], [columnHelper, onDelete, initials]);

  return (
    <DataTable
      columns={columns}
      data={data}
      isLoading={isLoading}
      isError={isError}
      noDataMessage="No users found"
      rowSelection={rowSelection}
      onRowSelectionChange={onRowSelectionChange}
    />
  );
}
