import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import BlogCategoryForm from '@/pages/blog-categories/components/BlogCategoryForm';
import { useBlogCategory } from '@/modules/blog-categories/hooks/useBlogCategory';
import { useBlogCategoryMutations } from '@/modules/blog-categories/hooks/useBlogCategoryMutations';
import type { UpdateBlogCategoryPayload } from '@/modules/blog-categories/types';

interface BlogCategoryEditPageProps {
  uuid: string;
}

export default function BlogCategoryEditPage({ uuid }: BlogCategoryEditPageProps): React.JSX.Element {
  const { data, isPending, isError } = useBlogCategory(uuid);
  const { updateBlogCategory } = useBlogCategoryMutations();
  const [form, setForm] = React.useState<UpdateBlogCategoryPayload>({
    blog_category_name: '',
    blog_category_description: '',
    blog_category_image: '',
  });
  const [errors, setErrors] = React.useState<Record<string, string>>({});

  React.useEffect(() => {
    if (!data) return;

    setForm({
      blog_category_name: data.blog_category_name,
      blog_category_description: data.blog_category_description ?? '',
      blog_category_image: data.blog_category_image ?? '',
    });
  }, [data]);

  function handleChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>): void {
    const { name, value } = event.target;

    setForm((previous) => ({ ...previous, [name]: value }));
    if (errors[name]) {
      setErrors((previous) => ({ ...previous, [name]: '' }));
    }
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    updateBlogCategory.mutate({ uuid, payload: form }, {
      onSuccess: () => {
        router.visit(`/blog-categories/${uuid}`);
      },
      onError: (error: unknown) => {
        if (typeof error !== 'object' || error === null || !('response' in error)) {
          return;
        }

        const response = (error as { response?: { data?: { errors?: Record<string, string[]> } } }).response;
        const serverErrors = response?.data?.errors;

        if (!serverErrors) {
          return;
        }

        const nextErrors: Record<string, string> = {};

        for (const [key, messages] of Object.entries(serverErrors)) {
          nextErrors[key] = messages[0] ?? '';
        }

        setErrors(nextErrors);
      },
    });
  }

  if (isPending) {
    return (
      <AppLayout>
        <Head title="Edit Blog Category" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--text-muted)' }}>
          Loading category…
        </div>
      </AppLayout>
    );
  }

  if (isError || !data) {
    return (
      <AppLayout>
        <Head title="Edit Blog Category" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--accent-error)' }}>
          Unable to load the selected category.
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <Head title={`Edit ${data.blog_category_name}`} />
      <BlogCategoryForm
        title="Edit Blog Category"
        description="Update the category metadata used across blog listings and admin tools."
        backHref={`/blog-categories/${uuid}`}
        submitLabel="Save Changes"
        isSubmitting={updateBlogCategory.isPending}
        form={form}
        errors={errors}
        onChange={handleChange}
        onSubmit={(event) => {
          void handleSubmit(event);
        }}
      />
    </AppLayout>
  );
}
