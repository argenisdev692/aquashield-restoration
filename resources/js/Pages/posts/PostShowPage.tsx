import * as React from 'react';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Pencil, RotateCcw, Trash2 } from 'lucide-react';
import AppLayout from '@/pages/layouts/AppLayout';
import { DeleteConfirmModal } from '@/shadcn/DeleteConfirmModal';
import { RestoreConfirmModal } from '@/shadcn/RestoreConfirmModal';
import PostContentPreview from '@/pages/posts/components/PostContentPreview';
import { usePost } from '@/modules/posts/hooks/usePost';
import { usePostMutations } from '@/modules/posts/hooks/usePostMutations';
import { formatDate } from '@/utils/dateFormatter';

interface PostShowPageProps {
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

export default function PostShowPage({ uuid }: PostShowPageProps): React.JSX.Element {
  const { data, isPending, isError } = usePost(uuid);
  const { deletePost, restorePost } = usePostMutations();
  const [deleteOpen, setDeleteOpen] = React.useState<boolean>(false);
  const [restoreOpen, setRestoreOpen] = React.useState<boolean>(false);

  async function handleDelete(): Promise<void> {
    await deletePost.mutateAsync(uuid);
    router.visit('/posts');
  }

  async function handleRestore(): Promise<void> {
    await restorePost.mutateAsync(uuid);
    setRestoreOpen(false);
  }

  if (isPending) {
    return (
      <AppLayout>
        <Head title="Post" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--text-muted)' }}>
          Loading post…
        </div>
      </AppLayout>
    );
  }

  if (isError || !data) {
    return (
      <AppLayout>
        <Head title="Post" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--accent-error)' }}>
          Unable to load the selected post.
        </div>
      </AppLayout>
    );
  }

  const isDeleted = data.deleted_at !== null;

  return (
    <AppLayout>
      <Head title={data.post_title} />
      <div className="mx-auto flex max-w-6xl flex-col gap-6">
        <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
          <div className="flex items-center gap-4">
            <Link
              href="/posts"
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
                {data.post_title}
              </h1>
              <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
                {isDeleted ? 'Archived post' : `${data.post_status} post`}
              </p>
            </div>
          </div>

          <div className="flex flex-wrap gap-3">
            {!isDeleted && (
              <Link
                href={`/posts/${uuid}/edit`}
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

        <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
          <div className="space-y-6 xl:col-span-2">
            <div
              className="rounded-3xl p-8 shadow-xl"
              style={{
                background: 'var(--bg-card)',
                border: '1px solid var(--border-default)',
              }}
            >
              <dl>
                <InfoRow label="Slug" value={data.post_title_slug} />
                <InfoRow label="Excerpt" value={data.post_excerpt} />
                <InfoRow label="Category" value={data.category_name} />
                <InfoRow label="Cover Image" value={data.post_cover_image} />
                <InfoRow label="Published" value={formatDate(data.published_at)} />
                <InfoRow label="Scheduled" value={formatDate(data.scheduled_at)} />
                <InfoRow label="Created" value={formatDate(data.created_at)} />
                <InfoRow label="Updated" value={formatDate(data.updated_at)} />
                <InfoRow label="Deleted" value={formatDate(data.deleted_at)} />
              </dl>
            </div>

            <div
              className="rounded-3xl p-8 shadow-xl"
              style={{
                background: 'var(--bg-card)',
                border: '1px solid var(--border-default)',
              }}
            >
              <h2 className="text-sm font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Content
              </h2>
              <div className="mt-5">
                <PostContentPreview value={data.post_content} />
              </div>
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
                <div>
                  <span style={{ color: 'var(--text-muted)' }}>Meta title</span>
                  <p className="mt-1">{data.meta_title || '—'}</p>
                </div>
                <div>
                  <span style={{ color: 'var(--text-muted)' }}>Meta description</span>
                  <p className="mt-1">{data.meta_description || '—'}</p>
                </div>
                <div>
                  <span style={{ color: 'var(--text-muted)' }}>Meta keywords</span>
                  <p className="mt-1 break-words">{data.meta_keywords || '—'}</p>
                </div>
              </div>
            </div>
          </div>
        </div>

        <DeleteConfirmModal
          open={deleteOpen}
          entityLabel={data.post_title}
          onConfirm={() => {
            void handleDelete();
          }}
          onCancel={() => setDeleteOpen(false)}
          isDeleting={deletePost.isPending}
        />

        <RestoreConfirmModal
          isOpen={restoreOpen}
          entityLabel="post"
          entityName={data.post_title}
          onConfirm={() => {
            void handleRestore();
          }}
          onCancel={() => setRestoreOpen(false)}
          isPending={restorePost.isPending}
        />
      </div>
    </AppLayout>
  );
}
