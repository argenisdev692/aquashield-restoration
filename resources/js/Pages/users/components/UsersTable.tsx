import * as React from 'react';
import { createColumnHelper, type ColumnDef, type RowSelectionState, type OnChangeFn } from '@tanstack/react-table';
import { Link } from '@inertiajs/react';
import { DataTable } from '@/shadcn/data-table';
import UserStatusBadge from '@/modules/users/components/UserStatusBadge';
import type { UserListItem } from '@/modules/users/types';
import { formatDateShort } from '@/utils/dateFormatter';

import { Eye, Pencil, Trash2, CheckCircle } from 'lucide-react';

const columnHelper = createColumnHelper<UserListItem>();

interface UsersTableProps {
  data: UserListItem[];
  isLoading: boolean;
  isError?: boolean;
  onDelete: (uuid: string, name: string, email: string) => void;
  onRestore: (uuid: string, name: string, email: string) => void;
  initials: (name: string, lastName: string) => string;
  rowSelection: RowSelectionState;
  onRowSelectionChange: OnChangeFn<RowSelectionState>;
}

export default function UsersTable({
  data,
  isLoading,
  isError = false,
  onDelete,
  onRestore,
  initials,
  rowSelection,
  onRowSelectionChange,
}: UsersTableProps): React.JSX.Element {
  const columns = React.useMemo(() => [
    columnHelper.display({
      id: 'select',
      header: ({ table }) => (
        <input
          type="checkbox"
          checked={table.getIsAllPageRowsSelected()}
          onChange={table.getToggleAllPageRowsSelectedHandler()}
          aria-label="Select all"
          className="h-4 w-4 cursor-pointer rounded accent-(--accent-primary)"
        />
      ),
      cell: ({ row }) => (
        <input
          type="checkbox"
          checked={row.getIsSelected()}
          onChange={row.getToggleSelectedHandler()}
          aria-label="Select row"
          className="h-4 w-4 cursor-pointer rounded accent-(--accent-primary)"
        />
      ),
    }),
    columnHelper.accessor('full_name', {
      header: 'User',
      cell: (info) => {
        const user = info.row.original;
        const displayName = user.full_name?.trim() || user.name || user.username || 'Unknown User';
        
        return (
          <div className="flex items-center gap-3 text-left">
            {user.profile_photo_path ? (
              <img
                src={user.profile_photo_path}
                alt={displayName}
                className="h-9 w-9 rounded-lg object-cover border border-(--border-default)"
              />
            ) : (
              <div
                className="flex h-9 w-9 items-center justify-center rounded-lg text-[11px] font-bold shadow-sm"
                style={{
                  background: 'var(--grad-primary)',
                  color: 'var(--color-white)',
                }}
              >
                {initials(user.name, user.last_name ?? '')}
              </div>
            )}
            <div>
              <p className="text-sm font-semibold leading-tight text-(--text-primary)">
                {displayName}
              </p>
              {user.username && user.full_name?.trim() && (
                <p className="text-[11px] mt-0.5" style={{ color: 'var(--text-disabled)' }}>
                  @{user.username}
                </p>
              )}
            </div>
          </div>
        );
      },
    }),
    columnHelper.accessor('email', {
      header: 'Email / Phone',
      cell: (info) => {
          const user = info.row.original;
          return (
              <div className="flex flex-col">
                  <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>{user.email || '—'}</span>
                  <span className="text-[11px]" style={{ color: 'var(--text-muted)' }}>{user.phone || ''}</span>
              </div>
          );
      }
    }),
    columnHelper.accessor('status', {
      header: 'Status',
      cell: (info) => <UserStatusBadge status={info.getValue()} />,
    }),
    columnHelper.accessor('created_at', {
      header: 'Created',
      cell: (info) => {
        const val = info.getValue() as string | undefined;
        return (
          <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
            {formatDateShort(val)}
          </span>
        );
      },
    }),
    columnHelper.display({
      id: 'actions',
      header: 'Actions',
      cell: (info) => {
        const user = info.row.original;
        const isDeleted = !!user.deleted_at;
        
        return (
          <div className="flex items-center justify-end gap-1.5">
            <Link
               href={`/users/${user.uuid}`}
               className="btn-action btn-action-view"
               title="View Profile"
               aria-label="View user"
            >
               <Eye size={14} />
            </Link>
            
            {!isDeleted && (
              <Link
                 href={`/users/${user.uuid}/edit`}
                 className="btn-action btn-action-edit"
                 title="Edit User"
                 aria-label="Edit user"
              >
                 <Pencil size={14} />
              </Link>
            )}

            {isDeleted ? (
              <button
                  onClick={() => onRestore(user.uuid, user.full_name, user.email)}
                  className="btn-action btn-action-restore"
                  title="Restore User"
                  aria-label="Restore user"
              >
                  <CheckCircle size={14} />
              </button>
            ) : (
              <button
                 onClick={() => onDelete(user.uuid, user.full_name, user.email)}
                 className="btn-action btn-action-delete"
                 title="Delete User"
                 aria-label="Delete user"
              >
                 <Trash2 size={14} />
              </button>
            )}
          </div>
        );
      },
    }),
  ] as ColumnDef<UserListItem>[], [initials, onDelete, onRestore]);

  return (
    <DataTable
      columns={columns}
      data={data}
      isLoading={isLoading}
      isError={isError}
      noDataMessage="No users found"
      getRowId={(row) => row.uuid}
      rowSelection={rowSelection}
      onRowSelectionChange={onRowSelectionChange}
    />
  );
}
