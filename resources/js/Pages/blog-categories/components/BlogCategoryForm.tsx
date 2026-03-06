import * as React from 'react';
import { Link } from '@inertiajs/react';
import { ArrowLeft, Save } from 'lucide-react';
import { PremiumField } from '@/shadcn/PremiumField';
import type { CreateBlogCategoryPayload, UpdateBlogCategoryPayload } from '@/modules/blog-categories/types';

interface BlogCategoryFormProps {
  title: string;
  description: string;
  backHref: string;
  submitLabel: string;
  isSubmitting: boolean;
  form: CreateBlogCategoryPayload | UpdateBlogCategoryPayload;
  errors: Record<string, string>;
  onChange: (event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>) => void;
  onSubmit: (event: React.FormEvent<HTMLFormElement>) => void;
}

export default function BlogCategoryForm({
  title,
  description,
  backHref,
  submitLabel,
  isSubmitting,
  form,
  errors,
  onChange,
  onSubmit,
}: BlogCategoryFormProps): React.JSX.Element {
  return (
    <form onSubmit={onSubmit} className="mx-auto flex max-w-4xl flex-col gap-8">
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
            color: '#ffffff',
            boxShadow: '0 10px 24px color-mix(in srgb, var(--accent-primary) 24%, transparent)',
          }}
        >
          <Save size={18} />
          <span>{isSubmitting ? 'Saving…' : submitLabel}</span>
        </button>
      </div>

      <div className="grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div className="space-y-6 lg:col-span-2">
          <div
            className="space-y-6 rounded-3xl p-8 shadow-xl"
            style={{
              background: 'var(--bg-card)',
              border: '1px solid var(--border-default)',
            }}
          >
            <PremiumField
              label="Category Name"
              name="blog_category_name"
              value={form.blog_category_name ?? ''}
              onChange={onChange}
              required
              error={errors.blog_category_name}
              placeholder="Emergency Tips"
            />

            <PremiumField
              label="Description"
              name="blog_category_description"
              value={form.blog_category_description ?? ''}
              onChange={onChange}
              error={errors.blog_category_description}
              placeholder="Short summary for editors and readers"
              isTextArea
            />

            <PremiumField
              label="Image URL"
              name="blog_category_image"
              value={form.blog_category_image ?? ''}
              onChange={onChange}
              error={errors.blog_category_image}
              placeholder="https://example.com/category-cover.jpg"
            />
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
              Publishing notes
            </h2>
            <div
              className="mt-4 rounded-2xl p-4"
              style={{
                background: 'var(--bg-card)',
                border: '1px solid var(--border-default)',
              }}
            >
              <p className="text-sm leading-6" style={{ color: 'var(--text-secondary)' }}>
                Keep the category name concise, use the description for editorial context, and attach an image URL only when the asset is final.
              </p>
            </div>
          </div>
        </div>
      </div>
    </form>
  );
}
