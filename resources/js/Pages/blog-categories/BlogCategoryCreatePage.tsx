import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import BlogCategoryForm from '@/pages/blog-categories/components/BlogCategoryForm';
import { useBlogCategoryMutations } from '@/modules/blog-categories/hooks/useBlogCategoryMutations';
import type { CreateBlogCategoryPayload } from '@/modules/blog-categories/types';

export default function BlogCategoryCreatePage(): React.JSX.Element {
  const [form, setForm] = React.useState<CreateBlogCategoryPayload>({
    blog_category_name: '',
    blog_category_description: '',
    blog_category_image: '',
  });
  const [errors, setErrors] = React.useState<Record<string, string>>({});
  const { createBlogCategory } = useBlogCategoryMutations();

  function handleChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement>): void {
    const { name, value } = event.target;

    setForm((previous) => ({ ...previous, [name]: value }));
    if (errors[name]) {
      setErrors((previous) => ({ ...previous, [name]: '' }));
    }
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    createBlogCategory.mutate(form, {
      onSuccess: () => {
        router.visit('/blog-categories');
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

  return (
    <AppLayout>
      <Head title="Create Blog Category" />
      <BlogCategoryForm
        title="New Blog Category"
        description="Create the first-level topic for your blog editorial flow."
        backHref="/blog-categories"
        submitLabel="Save Category"
        isSubmitting={createBlogCategory.isPending}
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
