import * as React from 'react';
import { Link } from '@inertiajs/react';
import { type CellContext, type ColumnDef, type HeaderContext, type OnChangeFn, type RowSelectionState } from '@tanstack/react-table';
import { CheckCircle, Eye, Pencil, Trash2 } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { PostListItem } from '@/modules/posts/types';

interface PostsTableProps {
  data: PostListItem[];
  isLoading: boolean;
  isError?: boolean;
  rowSelection: RowSelectionState;
  onRowSelectionChange: OnChangeFn<RowSelectionState>;
  onDelete: (item: PostListItem) => void;
  onRestore: (item: PostListItem) => void;
}

export default function PostsTable({
  data,
  isLoading,
  isError = false,
  rowSelection,
  onRowSelectionChange,
  onDelete,
  onRestore,
}: PostsTableProps): React.JSX.Element {
  const columns = React.useMemo(() => [
    {
      id: 'select',
      header: ({ table }: HeaderContext<PostListItem, unknown>) => (
        <input
          type="checkbox"
          checked={table.getIsAllPageRowsSelected()}
          onChange={table.getToggleAllPageRowsSelectedHandler()}
          aria-label="Select all posts"
          style={{ accentColor: 'var(--accent-primary)' }}
        />
      ),
      cell: ({ row }: CellContext<PostListItem, unknown>) => (
        <input
          type="checkbox"
          checked={row.getIsSelected()}
          onChange={row.getToggleSelectedHandler()}
          aria-label="Select post"
          style={{ accentColor: 'var(--accent-primary)' }}
        />
      ),
    },
    {
      accessorKey: 'post_title',
      header: 'Post',
      cell: ({ row }: CellContext<PostListItem, unknown>) => (
        <div className="flex flex-col items-start gap-1 text-left">
          <span className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
            {row.original.post_title}
          </span>
          <span className="text-xs" style={{ color: 'var(--text-muted)' }}>
            {row.original.post_excerpt || row.original.post_title_slug}
          </span>
        </div>
      ),
    },
    {
      accessorKey: 'category_name',
      header: 'Category',
      cell: ({ row }: CellContext<PostListItem, unknown>) => (
        <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>
          {row.original.category_name || '—'}
        </span>
      ),
    },
    {
      accessorKey: 'post_status',
      header: 'Status',
      cell: ({ row }: CellContext<PostListItem, unknown>) => {
        const status = row.original.deleted_at ? 'deleted' : row.original.post_status;
        const tone = row.original.deleted_at ? 'var(--accent-error)' : 'var(--accent-primary)';

        return (
          <span
            className="inline-flex rounded-full px-2.5 py-1 text-[11px] font-semibold uppercase tracking-wide"
            style={{
              background: `color-mix(in srgb, ${tone} 15%, transparent)`,
              border: `1px solid color-mix(in srgb, ${tone} 22%, transparent)`,
              color: tone,
            }}
          >
            {status}
          </span>
        );
      },
    },
    {
      accessorKey: 'published_at',
      header: 'Published',
      cell: ({ row }: CellContext<PostListItem, unknown>) => (
        <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
          {formatDateShort(row.original.published_at)}
        </span>
      ),
    },
    {
      accessorKey: 'created_at',
      header: 'Created',
      cell: ({ row }: CellContext<PostListItem, unknown>) => (
        <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
          {formatDateShort(row.original.created_at)}
        </span>
      ),
    },
    {
      id: 'actions',
      header: 'Actions',
      cell: ({ row }: CellContext<PostListItem, unknown>) => {
        const item = row.original;
        const isDeleted = item.deleted_at !== null;

        return (
          <div className="flex items-center justify-end gap-1.5">
            <Link href={`/posts/${item.uuid}`} className="btn-action btn-action-view" title="View post">
              <Eye size={14} />
            </Link>
            {!isDeleted ? (
              <>
                <Link href={`/posts/${item.uuid}/edit`} className="btn-action btn-action-edit" title="Edit post">
                  <Pencil size={14} />
                </Link>
                <button type="button" onClick={() => onDelete(item)} className="btn-action btn-action-delete" title="Delete post">
                  <Trash2 size={14} />
                </button>
              </>
            ) : (
              <button type="button" onClick={() => onRestore(item)} className="btn-action btn-action-restore" title="Restore post">
                <CheckCircle size={14} />
              </button>
            )}
          </div>
        );
      },
    },
  ], [onDelete, onRestore]) as ColumnDef<PostListItem, unknown>[];

  return (
    <DataTable
      columns={columns}
      data={data}
      isLoading={isLoading}
      isError={isError}
      noDataMessage="No posts found"
      rowSelection={rowSelection}
      onRowSelectionChange={onRowSelectionChange}
      getRowId={(row) => row.uuid}
    />
  );
}
