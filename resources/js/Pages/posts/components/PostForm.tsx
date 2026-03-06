import * as React from 'react';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { PremiumField } from '@/shadcn/PremiumField';
import { useBlogCategories } from '@/modules/blog-categories/hooks/useBlogCategories';
import type { CreatePostPayload, UpdatePostPayload } from '@/modules/posts/types';
import PostEditor from '@/pages/posts/components/PostEditor';

interface PostFormProps {
  title: string;
  description: string;
  backHref: string;
  submitLabel: string;
  isSubmitting: boolean;
  form: CreatePostPayload | UpdatePostPayload;
  errors: Record<string, string>;
  onChange: (event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>) => void;
  onContentChange: (value: string) => void;
  onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
}

export default function PostForm({
  title,
  description,
  backHref,
  submitLabel,
  isSubmitting,
  form,
  errors,
  onChange,
  onContentChange,
  onSubmit,
}: PostFormProps): React.JSX.Element {
  const { data: categoriesResponse } = useBlogCategories({ per_page: 100, status: 'active' });
  const categories = categoriesResponse?.data ?? [];
  const status = form.post_status ?? 'draft';

  return (
    <form onSubmit={onSubmit} className="mx-auto flex max-w-7xl flex-col gap-8">
      <div className="flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div className="flex items-center gap-4">
          <Link
            href={backHref}
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
              {title}
            </h1>
            <p className="text-sm" style={{ color: 'var(--text-muted)' }}>
              {description}
            </p>
          </div>
        </div>

        <button
          type="submit"
          disabled={isSubmitting}
          className="flex items-center justify-center gap-2 rounded-xl px-5 py-2.5 text-sm font-semibold transition-all disabled:opacity-50"
          style={{
            background: 'var(--grad-primary)',
            color: 'var(--color-white)',
            boxShadow: '0 10px 24px color-mix(in srgb, var(--accent-primary) 24%, transparent)',
          }}
        >
          <Save size={18} />
          <span>{isSubmitting ? 'Saving…' : submitLabel}</span>
        </button>
      </div>

      <div className="grid grid-cols-1 gap-6 xl:grid-cols-3">
        <div className="space-y-6 xl:col-span-2">
          <div
            className="space-y-6 rounded-3xl p-8 shadow-xl"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
            }}
          >
            <PremiumField
              label="Post Title"
              name="post_title"
              value={form.post_title ?? ''}
              onChange={onChange}
              required
              error={errors.post_title}
              placeholder="Storm preparation checklist"
            />

            <PremiumField
              label="Slug"
              name="post_title_slug"
              value={form.post_title_slug ?? ''}
              onChange={onChange}
              error={errors.post_title_slug}
              placeholder="storm-preparation-checklist"
            />

            <PremiumField
              label="Excerpt"
              name="post_excerpt"
              value={form.post_excerpt ?? ''}
              onChange={onChange}
              error={errors.post_excerpt}
              placeholder="Short summary used on cards and previews"
              isTextArea
            />

            <div className="space-y-2">
              <span className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Post Content
              </span>
              <PostEditor
                value={form.post_content ?? ''}
                onChange={onContentChange}
                disabled={isSubmitting}
                placeholder="Write the main story, guidance or update here..."
              />
              {errors.post_content ? (
                <span className="text-[11px] font-medium" style={{ color: 'var(--accent-error)' }}>
                  {errors.post_content}
                </span>
              ) : null}
            </div>
          </div>

          <div
            className="space-y-6 rounded-3xl p-8 shadow-xl"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
            }}
          >
            <h2 className="text-sm font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
              SEO Metadata
            </h2>

            <PremiumField
              label="Meta Title"
              name="meta_title"
              value={form.meta_title ?? ''}
              onChange={onChange}
              error={errors.meta_title}
              placeholder="Search result title"
            />

            <PremiumField
              label="Meta Description"
              name="meta_description"
              value={form.meta_description ?? ''}
              onChange={onChange}
              error={errors.meta_description}
              placeholder="Concise description for search previews"
              isTextArea
            />

            <PremiumField
              label="Meta Keywords"
              name="meta_keywords"
              value={form.meta_keywords ?? ''}
              onChange={onChange}
              error={errors.meta_keywords}
              placeholder="restoration, emergency, water damage"
            />
          </div>
        </div>

        <div className="space-y-6">
          <div
            className="space-y-5 rounded-3xl p-6"
            style={{
              background: 'var(--bg-surface)',
              border: '1px solid var(--border-subtle)',
            }}
          >
            <h2 className="text-sm font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
              Publishing
            </h2>

            <label className="flex flex-col gap-2">
              <span className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Status
              </span>
              <select
                name="post_status"
                value={status}
                onChange={onChange}
                className="h-11 rounded-xl border px-4 text-sm outline-none"
                style={{
                  background: 'var(--bg-card)',
                  borderColor: errors.post_status ? 'var(--accent-error)' : 'var(--border-default)',
                  color: 'var(--text-primary)',
                }}
              >
                <option value="draft">Draft</option>
                <option value="published">Published</option>
                <option value="scheduled">Scheduled</option>
                <option value="archived">Archived</option>
              </select>
              {errors.post_status ? (
                <span className="text-[11px] font-medium" style={{ color: 'var(--accent-error)' }}>
                  {errors.post_status}
                </span>
              ) : null}
            </label>

            <label className="flex flex-col gap-2">
              <span className="text-[11px] font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
                Category
              </span>
              <select
                name="category_uuid"
                value={form.category_uuid ?? ''}
                onChange={onChange}
                className="h-11 rounded-xl border px-4 text-sm outline-none"
                style={{
                  background: 'var(--bg-card)',
                  borderColor: errors.category_uuid ? 'var(--accent-error)' : 'var(--border-default)',
                  color: 'var(--text-primary)',
                }}
              >
                <option value="">No category</option>
                {categories.map((category) => (
                  <option key={category.uuid} value={category.uuid}>
                    {category.blog_category_name}
                  </option>
                ))}
              </select>
              {errors.category_uuid ? (
                <span className="text-[11px] font-medium" style={{ color: 'var(--accent-error)' }}>
                  {errors.category_uuid}
                </span>
              ) : null}
            </label>

            <PremiumField
              label="Cover Image URL"
              name="post_cover_image"
              value={form.post_cover_image ?? ''}
              onChange={onChange}
              error={errors.post_cover_image}
              placeholder="https://example.com/cover.jpg"
            />

            <PremiumField
              label="Published At"
              name="published_at"
              type="datetime-local"
              value={form.published_at ?? ''}
              onChange={onChange}
              error={errors.published_at}
            />

            <PremiumField
              label="Scheduled At"
              name="scheduled_at"
              type="datetime-local"
              value={form.scheduled_at ?? ''}
              onChange={onChange}
              error={errors.scheduled_at}
            />
          </div>

          <div
            className="rounded-3xl p-6"
            style={{
              background: 'var(--bg-surface)',
              border: '1px solid var(--border-subtle)',
            }}
          >
            <h2 className="text-sm font-bold uppercase tracking-widest" style={{ color: 'var(--text-muted)' }}>
              Editorial notes
            </h2>
            <div
              className="mt-4 rounded-2xl p-4"
              style={{
                background: 'var(--bg-card)',
                border: '1px solid var(--border-default)',
              }}
            >
              <p className="text-sm leading-6" style={{ color: 'var(--text-secondary)' }}>
                Draft keeps the post internal, published makes it live, and scheduled lets you prepare future releases with a target date.
              </p>
            </div>
          </div>
        </div>
      </div>
    </form>
  );
}
