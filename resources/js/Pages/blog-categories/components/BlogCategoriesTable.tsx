import * as React from 'react';
import { Link } from '@inertiajs/react';
import { type CellContext, type ColumnDef, type HeaderContext, type OnChangeFn, type RowSelectionState } from '@tanstack/react-table';
import { Eye, Pencil, Trash2, CheckCircle } from 'lucide-react';
import { DataTable } from '@/shadcn/data-table';
import { formatDateShort } from '@/utils/dateFormatter';
import type { BlogCategoryListItem } from '@/modules/blog-categories/types';

interface BlogCategoriesTableProps {
  data: BlogCategoryListItem[];
  isLoading: boolean;
  isError?: boolean;
  rowSelection: RowSelectionState;
  onRowSelectionChange: OnChangeFn<RowSelectionState>;
  onDelete: (item: BlogCategoryListItem) => void;
  onRestore: (item: BlogCategoryListItem) => void;
}

export default function BlogCategoriesTable({
  data,
  isLoading,
  isError = false,
  rowSelection,
  onRowSelectionChange,
  onDelete,
  onRestore,
}: BlogCategoriesTableProps): React.JSX.Element {
  const columns = React.useMemo(() => [
    {
      id: 'select',
      header: ({ table }: HeaderContext<BlogCategoryListItem, unknown>) => (
        <input
          type="checkbox"
          checked={table.getIsAllPageRowsSelected()}
          onChange={table.getToggleAllPageRowsSelectedHandler()}
          aria-label="Select all categories"
          style={{ accentColor: 'var(--accent-primary)' }}
        />
      ),
      cell: ({ row }: CellContext<BlogCategoryListItem, unknown>) => (
        <input
          type="checkbox"
          checked={row.getIsSelected()}
          onChange={row.getToggleSelectedHandler()}
          aria-label="Select category"
          style={{ accentColor: 'var(--accent-primary)' }}
        />
      ),
    },
    {
      accessorKey: 'blog_category_name',
      header: 'Category',
      cell: ({ row }: CellContext<BlogCategoryListItem, unknown>) => {
        const item = row.original;

        return (
          <div className="flex flex-col items-start gap-1 text-left">
            <span className="text-sm font-semibold" style={{ color: 'var(--text-primary)' }}>
              {item.blog_category_name}
            </span>
            <span className="text-xs" style={{ color: 'var(--text-muted)' }}>
              {item.blog_category_description || 'No description'}
            </span>
          </div>
        );
      },
    },
    {
      accessorKey: 'blog_category_image',
      header: 'Image',
      cell: ({ row }: CellContext<BlogCategoryListItem, unknown>) => (
        <span className="text-sm" style={{ color: 'var(--text-secondary)' }}>
          {row.original.blog_category_image ? 'Available' : '—'}
        </span>
      ),
    },
    {
      accessorKey: 'created_at',
      header: 'Created',
      cell: ({ row }: CellContext<BlogCategoryListItem, unknown>) => (
        <span className="text-sm" style={{ color: 'var(--text-muted)' }}>
          {formatDateShort(row.original.created_at)}
        </span>
      ),
    },
    {
      id: 'actions',
      header: 'Actions',
      cell: ({ row }: CellContext<BlogCategoryListItem, unknown>) => {
        const item = row.original;
        const isDeleted = item.deleted_at !== null;

        return (
          <div className="flex items-center justify-end gap-1.5">
            <Link href={`/blog-categories/${item.uuid}`} className="btn-action btn-action-view" title="View category">
              <Eye size={14} />
            </Link>
            {!isDeleted && (
              <Link href={`/blog-categories/${item.uuid}/edit`} className="btn-action btn-action-edit" title="Edit category">
                <Pencil size={14} />
              </Link>
            )}
            {isDeleted ? (
              <button type="button" onClick={() => onRestore(item)} className="btn-action btn-action-restore" title="Restore category">
                <CheckCircle size={14} />
              </button>
            ) : (
              <button type="button" onClick={() => onDelete(item)} className="btn-action btn-action-delete" title="Delete category">
                <Trash2 size={14} />
              </button>
            )}
          </div>
        );
      },
    },
  ], [onDelete, onRestore]) as unknown as ColumnDef<BlogCategoryListItem, unknown>[];

  return (
    <DataTable
      columns={columns}
      data={data}
      isLoading={isLoading}
      isError={isError}
      noDataMessage="No blog categories found"
      rowSelection={rowSelection}
      onRowSelectionChange={onRowSelectionChange}
    />
  );
}
