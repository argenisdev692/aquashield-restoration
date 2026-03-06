import * as React from 'react';
import { Head, Link, useRemember } from '@inertiajs/react';
import type { RowSelectionState } from '@tanstack/react-table';
import { ChevronLeft, ChevronRight, FilePlus2, Search } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { ExportButton } from '@/common/export/ExportButton';
import { DataTableBulkActions } from '@/shadcn/DataTableBulkActions';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import PostsTable from '@/pages/posts/components/PostsTable';
import { usePosts } from '@/modules/posts/hooks/usePosts';
import { usePostMutations } from '@/modules/posts/hooks/usePostMutations';
import type { PostFilters, PostListItem } from '@/modules/posts/types';

export default function PostsIndexPage(): React.JSX.Element {
  const [filters, setFilters] = useRemember<PostFilters>({ page: 1, per_page: 15, status: '' }, 'posts-filters');
  const [search, setSearch] = React.useState<string>(filters.search ?? '');
  const [rowSelection, setRowSelection] = React.useState<RowSelectionState>({});
  const [pendingDelete, setPendingDelete] = React.useState<PostListItem | null>(null);
  const [pendingRestore, setPendingRestore] = React.useState<PostListItem | null>(null);
  const [isPendingExport, startExportTransition] = React.useTransition();
  const [, startSearchTransition] = React.useTransition();

  const { data, isPending, isError } = usePosts(filters);
  const { deletePost, restorePost, bulkDeletePosts } = usePostMutations();

  const posts = data?.data ?? [];
  const meta = data?.meta ?? { currentPage: 1, lastPage: 1, perPage: 15, total: 0 };

  const selectedUuids = React.useMemo(
    () => Object.keys(rowSelection)
      .filter((key) => rowSelection[key])
      .filter((uuid): uuid is string => typeof uuid === 'string' && uuid.length > 0),
    [rowSelection],
  );

  function handleSearchChange(event: React.ChangeEvent<HTMLInputElement>): void {
    const value = event.target.value;
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

      window.open(`/posts/data/admin/export?${params.toString()}`, '_blank');
    });
  }

  async function confirmDelete(): Promise<void> {
    if (!pendingDelete) return;

    await deletePost.mutateAsync(pendingDelete.uuid);
    setPendingDelete(null);
  }

  async function confirmRestore(): Promise<void> {
    if (!pendingRestore) return;

    await restorePost.mutateAsync(pendingRestore.uuid);
    setPendingRestore(null);
  }

  async function handleBulkDelete(): Promise<void> {
    if (selectedUuids.length === 0) return;

    await bulkDeletePosts.mutateAsync(selectedUuids);
    setRowSelection({});
  }

  function goToPage(page: number): void {
    setFilters((previous) => ({ ...previous, page }));
  }

  return (
    <>
      <Head title="Posts" />
      <AppLayout>
        <div className="flex flex-col gap-6">
          <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
            <div>
              <h1 className="text-3xl font-extrabold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                Posts
              </h1>
              <p className="mt-1 text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
                Manage editorial content and publishing states — <span style={{ color: 'var(--accent-primary)' }}>{meta.total}</span> posts registered.
              </p>
            </div>

            <Link
              href="/posts/create"
              className="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all"
              style={{
                background: 'var(--grad-primary)',
                color: 'var(--color-white)',
                boxShadow: '0 10px 24px color-mix(in srgb, var(--accent-primary) 24%, transparent)',
              }}
            >
              <FilePlus2 size={18} />
              <span>New Post</span>
            </Link>
          </div>

          <div
            className="flex flex-col gap-4 rounded-3xl px-5 py-4 shadow-sm lg:flex-row lg:items-end lg:justify-between"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
            }}
          >
            <div className="flex flex-1 items-center gap-3 rounded-2xl px-4 py-3" style={{ background: 'var(--bg-surface)' }}>
              <Search size={18} style={{ color: 'var(--text-disabled)' }} />
              <input
                type="text"
                value={search}
                onChange={handleSearchChange}
                placeholder="Search by title, excerpt or content"
                className="w-full bg-transparent text-sm outline-none"
                style={{ color: 'var(--text-primary)' }}
              />
            </div>

            <div className="grid grid-cols-1 gap-3 sm:grid-cols-2 xl:flex xl:items-end">
              <label className="flex flex-col gap-2 text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Status
                <select
                  value={filters.status || ''}
                  onChange={(event) => setFilters((previous) => ({ ...previous, status: event.target.value as PostFilters['status'], page: 1 }))}
                  className="h-10 rounded-xl border px-3 text-sm outline-none"
                  style={{
                    background: 'var(--bg-surface)',
                    borderColor: 'var(--border-default)',
                    color: 'var(--text-primary)',
                  }}
                >
                  <option value="">All</option>
                  <option value="draft">Draft</option>
                  <option value="published">Published</option>
                  <option value="scheduled">Scheduled</option>
                  <option value="archived">Archived</option>
                  <option value="deleted">Deleted</option>
                </select>
              </label>

              <label className="flex flex-col gap-2 text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                From
                <input
                  type="date"
                  value={filters.date_from || ''}
                  onChange={(event) => setFilters((previous) => ({ ...previous, date_from: event.target.value || undefined, page: 1 }))}
                  className="h-10 rounded-xl border px-3 text-sm outline-none"
                  style={{
                    background: 'var(--bg-surface)',
                    borderColor: 'var(--border-default)',
                    color: 'var(--text-primary)',
                  }}
                />
              </label>

              <label className="flex flex-col gap-2 text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                To
                <input
                  type="date"
                  value={filters.date_to || ''}
                  onChange={(event) => setFilters((previous) => ({ ...previous, date_to: event.target.value || undefined, page: 1 }))}
                  className="h-10 rounded-xl border px-3 text-sm outline-none"
                  style={{
                    background: 'var(--bg-surface)',
                    borderColor: 'var(--border-default)',
                    color: 'var(--text-primary)',
                  }}
                />
              </label>

              <div className="flex items-end">
                <ExportButton onExport={handleExport} isExporting={isPendingExport} />
              </div>
            </div>
          </div>

          <DataTableBulkActions
            count={selectedUuids.length}
            onDelete={() => {
              void handleBulkDelete();
            }}
            isDeleting={bulkDeletePosts.isPending}
          />

          <div
            className="overflow-hidden rounded-3xl shadow-xl"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
            }}
          >
            <PostsTable
              data={posts}
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
          entityLabel={pendingDelete?.post_title ?? ''}
          onConfirm={() => {
            void confirmDelete();
          }}
          onCancel={() => setPendingDelete(null)}
          isDeleting={deletePost.isPending}
        />

        <RestoreConfirmModal
          isOpen={pendingRestore !== null}
          entityLabel="post"
          entityName={pendingRestore?.post_title}
          onConfirm={() => {
            void confirmRestore();
          }}
          onCancel={() => setPendingRestore(null)}
          isPending={restorePost.isPending}
        />
      </AppLayout>
    </>
  );
}
