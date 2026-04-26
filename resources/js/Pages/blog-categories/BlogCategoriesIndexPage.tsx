import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, FolderPlus } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { CrudFilterBar } from '@/common/filters/CrudFilterBar';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { ExportButton } from '@/common/export/ExportButton';
import { useBlogCategories } from '@/modules/blog-categories/hooks/useBlogCategories';
import { useBlogCategoryMutations } from '@/modules/blog-categories/hooks/useBlogCategoryMutations';
import type { BlogCategoryFilters, BlogCategoryListItem } from '@/modules/blog-categories/types';
import BlogCategoriesTable from '@/pages/blog-categories/components/BlogCategoriesTable';

export default function BlogCategoriesIndexPage(): React.JSX.Element {
  const [filters, setFilters] = useRemember<BlogCategoryFilters>({ page: 1, per_page: 15 }, 'blog-categories-filters');
  const [search, setSearch] = React.useState<string>(filters.search ?? '');
  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
  const [pendingDelete, setPendingDelete] = React.useState<BlogCategoryListItem | null>(null);
  const [pendingRestore, setPendingRestore] = React.useState<BlogCategoryListItem | null>(null);
  const [isPendingExport, startExportTransition] = React.useTransition();
  const [, startSearchTransition] = React.useTransition();

  const { data, isPending, isError } = useBlogCategories(filters);
  const { deleteBlogCategory, restoreBlogCategory, bulkDeleteBlogCategories } = useBlogCategoryMutations();

  const categories = data?.data ?? [];
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

  const selectedUuids = React.useMemo(
    () => Object.keys(rowSelection)
      .filter((key) => rowSelection[key])
      .map((key) => categories[Number(key)]?.uuid)
      .filter((uuid): uuid is string => typeof uuid === 'string'),
    [categories, rowSelection],
  );

  function handleSearchChange(value: string): void {
    setSearch(value);

    startSearchTransition(() => {
      setFilters((previous) => ({ ...previous, search: value || undefined, page: 1 }));
    });
  }

  function handleExport(format: 'excel' | 'pdf'): void {
    startExportTransition(() => {
      const params = new URLSearchParams();

      if (filters.search) params.append('search', filters.search);
      if (filters.status) params.append('status', filters.status);
      if (filters.date_from) params.append('date_from', filters.date_from);
      if (filters.date_to) params.append('date_to', filters.date_to);
      params.append('format', format);

      window.open(`/blog-categories/data/admin/export?${params.toString()}`, '_blank');
    });
  }

  async function confirmDelete(): Promise<void> {
    if (!pendingDelete) return;

    await deleteBlogCategory.mutateAsync(pendingDelete.uuid);
    setPendingDelete(null);
  }

  async function confirmRestore(): Promise<void> {
    if (!pendingRestore) return;

    await restoreBlogCategory.mutateAsync(pendingRestore.uuid);
    setPendingRestore(null);
  }

  async function handleBulkDelete(): Promise<void> {
    if (selectedUuids.length === 0) return;

    await bulkDeleteBlogCategories.mutateAsync(selectedUuids);
    setRowSelection({});
  }

  function goToPage(page: number): void {
    setFilters((previous) => ({ ...previous, page }));
  }

  return (
    <>
      <Head title="Blog Categories" />
      <AppLayout>
        <div className="flex flex-col gap-6">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                Blog Categories
              </h1>
              <p className="mt-1 text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                Organize editorial topics across the blog — <span style={{ color: 'var(--accent-primary)' }}>{meta.total}</span> categories registered.
              </p>
            </div>

            <Link
              href="/blog-categories/create"
              className="btn-primary inline-flex items-center justify-center gap-2 rounded-xl px-5 py-3 text-sm font-semibold"
            >
              <FolderPlus size={18} />
              <span>New Category</span>
            </Link>
          </div>

          <CrudFilterBar
            searchValue={search}
            onSearchChange={handleSearchChange}
            searchPlaceholder="Search by name or description"
            searchAriaLabel="Search blog categories"
            statusValue={filters.status || ''}
            onStatusChange={(value) => {
              startSearchTransition(() => {
                setFilters((previous) => ({ ...previous, status: value as BlogCategoryFilters['status'], page: 1 }));
              });
            }}
            dateFrom={filters.date_from}
            dateTo={filters.date_to}
            onDateRangeChange={(range) => {
              startSearchTransition(() => {
                setFilters((previous) => ({
                  ...previous,
                  date_from: range.dateFrom,
                  date_to: range.dateTo,
                  page: 1,
                }));
              });
            }}
            actions={<ExportButton onExport={handleExport} isExporting={isPendingExport} />}
          />

          <DataTableBulkActions
            count={selectedUuids.length}
            onDelete={() => {
              void handleBulkDelete();
            }}
            isDeleting={bulkDeleteBlogCategories.isPending}
          />

          <div
            className="overflow-hidden rounded-3xl shadow-xl"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
            }}
          >
            <BlogCategoriesTable
              data={categories}
              isLoading={isPending}
              isError={isError}
              rowSelection={rowSelection}
              onRowSelectionChange={setRowSelection}
              onDelete={setPendingDelete}
              onRestore={setPendingRestore}
            />

            {meta.lastPage > 1 && (
              <div
                className="flex flex-col gap-3 px-6 py-4 sm:flex-row sm:items-center sm:justify-between"
                style={{
                  background: 'var(--bg-surface)',
                  borderTop: '1px solid var(--border-subtle)',
                }}
              >
                <span className="text-xs font-semibold uppercase tracking-wider" style={{ color: 'var(--text-disabled)' }}>
                  Page {meta.currentPage} / {meta.lastPage} • {meta.total} total
                </span>

                <div className="flex items-center gap-2">
                  <button
                    type="button"
                    onClick={() => goToPage(meta.currentPage - 1)}
                    disabled={meta.currentPage <= 1}
                    className="flex h-9 w-9 items-center justify-center rounded-xl border transition-all disabled:opacity-40"
                    style={{
                      background: 'var(--bg-card)',
                      borderColor: 'var(--border-default)',
                      color: 'var(--text-muted)',
                    }}
                  >
                    <ChevronLeft size={18} />
                  </button>

                  <button
                    type="button"
                    onClick={() => goToPage(meta.currentPage + 1)}
                    disabled={meta.currentPage >= meta.lastPage}
                    className="flex h-9 w-9 items-center justify-center rounded-xl border transition-all disabled:opacity-40"
                    style={{
                      background: 'var(--bg-card)',
                      borderColor: 'var(--border-default)',
                      color: 'var(--text-muted)',
                    }}
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
          entityLabel={pendingDelete?.blog_category_name ?? ''}
          onConfirm={() => {
            void confirmDelete();
          }}
          onCancel={() => setPendingDelete(null)}
          isDeleting={deleteBlogCategory.isPending}
        />

        <RestoreConfirmModal
          isOpen={pendingRestore !== null}
          entityLabel="category"
          entityName={pendingRestore?.blog_category_name}
          onConfirm={() => {
            void confirmRestore();
          }}
          onCancel={() => setPendingRestore(null)}
          isPending={restoreBlogCategory.isPending}
        />
      </AppLayout>
    </>
  );
}
