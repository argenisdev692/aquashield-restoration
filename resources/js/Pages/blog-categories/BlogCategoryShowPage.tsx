import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Pencil, Trash2, RotateCcw } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import { useBlogCategory } from '@/modules/blog-categories/hooks/useBlogCategory';
import { useBlogCategoryMutations } from '@/modules/blog-categories/hooks/useBlogCategoryMutations';
import { formatDate } from '@/utils/dateFormatter';

interface BlogCategoryShowPageProps {
  uuid: string;
}

function InfoRow({ label, value }: { label: string; value: string | null | undefined }): React.JSX.Element {
  return (
    <div className="grid grid-cols-1 gap-2 py-3 sm:grid-cols-3" style={{ borderBottom: '1px solid var(--border-subtle)' }}>
      <dt className="text-sm font-medium" style={{ color: 'var(--text-muted)' }}>
        {label}
      </dt>
      <dd className="text-sm font-medium sm:col-span-2" style={{ color: 'var(--text-primary)' }}>
        {value || '—'}
      </dd>
    </div>
  );
}

export default function BlogCategoryShowPage({ uuid }: BlogCategoryShowPageProps): React.JSX.Element {
  const { data, isPending, isError } = useBlogCategory(uuid);
  const { deleteBlogCategory, restoreBlogCategory } = useBlogCategoryMutations();
  const [deleteOpen, setDeleteOpen] = React.useState<boolean>(false);
  const [restoreOpen, setRestoreOpen] = React.useState<boolean>(false);

  async function handleDelete(): Promise<void> {
    await deleteBlogCategory.mutateAsync(uuid);
    router.visit('/blog-categories');
  }

  async function handleRestore(): Promise<void> {
    await restoreBlogCategory.mutateAsync(uuid);
    setRestoreOpen(false);
  }

  if (isPending) {
    return (
      <AppLayout>
        <Head title="Blog Category" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--text-muted)' }}>
          Loading category…
        </div>
      </AppLayout>
    );
  }

  if (isError || !data) {
    return (
      <AppLayout>
        <Head title="Blog Category" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--accent-error)' }}>
          Unable to load the selected category.
        </div>
      </AppLayout>
    );
  }

  const isDeleted = data.deleted_at !== null;

  return (
    <AppLayout>
      <Head title={data.blog_category_name} />
      <div className="mx-auto flex max-w-5xl flex-col gap-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-4">
            <Link
              href="/blog-categories"
              className="flex h-10 w-10 items-center justify-center rounded-xl border shadow-sm transition-all"
              style={{
                background: 'var(--bg-card)',
                borderColor: 'var(--border-default)',
                color: 'var(--text-muted)',
              }}
            >
              <ArrowLeft size={18} />
            </Link>
            <div>
              <h1 className="text-2xl font-bold tracking-tight" style={{ color: 'var(--text-primary)' }}>
                {data.blog_category_name}
              </h1>
              <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                {isDeleted ? 'Archived category' : 'Active blog category'}
              </p>
            </div>
          </div>

          <div className="flex flex-wrap gap-3">
            {!isDeleted && (
              <Link
                href={`/blog-categories/${uuid}/edit`}
                className="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all"
                style={{
                  background: 'var(--bg-card)',
                  border: '1px solid var(--border-default)',
                  color: 'var(--text-primary)',
                }}
              >
                <Pencil size={16} />
                <span>Edit</span>
              </Link>
            )}

            {isDeleted ? (
              <button
                type="button"
                onClick={() => setRestoreOpen(true)}
                className="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all"
                style={{
                  background: 'color-mix(in srgb, var(--success-primary) 12%, transparent)',
                  border: '1px solid color-mix(in srgb, var(--success-primary) 25%, var(--border-default))',
                  color: 'var(--success-primary)',
                }}
              >
                <RotateCcw size={16} />
                <span>Restore</span>
              </button>
            ) : (
              <button
                type="button"
                onClick={() => setDeleteOpen(true)}
                className="flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold transition-all"
                style={{
                  background: 'color-mix(in srgb, var(--accent-error) 12%, transparent)',
                  border: '1px solid color-mix(in srgb, var(--accent-error) 25%, var(--border-default))',
                  color: 'var(--accent-error)',
                }}
              >
                <Trash2 size={16} />
                <span>Delete</span>
              </button>
            )}
          </div>
        </div>

        <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
          <div className="space-y-6 lg:col-span-2">
            <div
              className="rounded-3xl p-8 shadow-xl"
              style={{
                background: 'var(--bg-card)',
                border: '1px solid var(--border-default)',
              }}
            >
              <dl>
                <InfoRow label="Name" value={data.blog_category_name} />
                <InfoRow label="Description" value={data.blog_category_description} />
                <InfoRow label="Image URL" value={data.blog_category_image} />
                <InfoRow label="Created" value={formatDate(data.created_at)} />
                <InfoRow label="Updated" value={formatDate(data.updated_at)} />
                <InfoRow label="Deleted" value={formatDate(data.deleted_at)} />
              </dl>
            </div>
          </div>

          <div className="space-y-6">
            <div
              className="rounded-3xl p-6"
              style={{
                background: 'var(--bg-surface)',
                border: '1px solid var(--border-subtle)',
              }}
            >
              <h2 className="text-sm font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Metadata
              </h2>
              <div className="mt-4 space-y-3 text-sm" style={{ color: 'var(--text-secondary)' }}>
                <div>
                  <span style={{ color: 'var(--text-muted)' }}>UUID</span>
                  <p className="mt-1 break-all">{data.uuid}</p>
                </div>
                <div>
                  <span style={{ color: 'var(--text-muted)' }}>Owner user ID</span>
                  <p className="mt-1">{data.user_id ?? '—'}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <DeleteConfirmModal
          open={deleteOpen}
          entityLabel={data.blog_category_name}
          onConfirm={() => {
            void handleDelete();
          }}
          onCancel={() => setDeleteOpen(false)}
          isDeleting={deleteBlogCategory.isPending}
        />

        <RestoreConfirmModal
          isOpen={restoreOpen}
          entityLabel="category"
          entityName={data.blog_category_name}
          onConfirm={() => {
            void handleRestore();
          }}
          onCancel={() => setRestoreOpen(false)}
          isPending={restoreBlogCategory.isPending}
        />
      </div>
    </AppLayout>
  );
}
