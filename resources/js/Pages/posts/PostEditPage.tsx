import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import PostForm from '@/pages/posts/components/PostForm';
import { usePost } from '@/modules/posts/hooks/usePost';
import { usePostMutations } from '@/modules/posts/hooks/usePostMutations';
import type { UpdatePostPayload } from '@/modules/posts/types';

interface PostEditPageProps {
  uuid: string;
}

function toDateTimeLocal(value: string | null | undefined): string {
  if (!value) {
    return '';
  }

  const date = new Date(value);

  if (Number.isNaN(date.getTime())) {
    return '';
  }

  const timezoneOffset = date.getTimezoneOffset() * 60000;

  return new Date(date.getTime() - timezoneOffset).toISOString().slice(0, 16);
}

function normalizeServerErrors(error: unknown): Record<string, string> {
  if (typeof error !== 'object' || error === null || !('response' in error)) {
    return {};
  }

  const response = (error as { response?: { data?: { errors?: Record<string, string[]> } } }).response;
  const serverErrors = response?.data?.errors;

  if (!serverErrors) {
    return {};
  }

  const nextErrors: Record<string, string> = {};

  for (const [key, messages] of Object.entries(serverErrors)) {
    nextErrors[key] = messages[0] ?? '';
  }

  return nextErrors;
}

export default function PostEditPage({ uuid }: PostEditPageProps): React.JSX.Element {
  const { data, isPending, isError } = usePost(uuid);
  const { updatePost } = usePostMutations();
  const [form, setForm] = React.useState<UpdatePostPayload>({
    post_title: '',
    post_title_slug: '',
    post_content: '<p></p>',
    post_excerpt: '',
    post_cover_image: '',
    meta_title: '',
    meta_description: '',
    meta_keywords: '',
    category_uuid: '',
    post_status: 'draft',
    scheduled_at: '',
  });
  const [errors, setErrors] = React.useState<Record<string, string>>({});

  React.useEffect(() => {
    if (!data) {
      return;
    }

    setForm({
      post_title: data.post_title,
      post_title_slug: data.post_title_slug,
      post_content: data.post_content,
      post_excerpt: data.post_excerpt ?? '',
      post_cover_image: data.post_cover_image ?? '',
      meta_title: data.meta_title ?? '',
      meta_description: data.meta_description ?? '',
      meta_keywords: data.meta_keywords ?? '',
      category_uuid: data.category_uuid ?? '',
      post_status: data.post_status,
      scheduled_at: toDateTimeLocal(data.scheduled_at),
    });
  }, [data]);

  function handleChange(event: React.ChangeEvent<HTMLInputElement | HTMLTextAreaElement | HTMLSelectElement>): void {
    const { name, value } = event.target;

    setForm((previous) => ({ ...previous, [name]: value }));
    if (errors[name]) {
      setErrors((previous) => ({ ...previous, [name]: '' }));
    }
  }

  function handleContentChange(value: string): void {
    setForm((previous) => ({ ...previous, post_content: value }));
    if (errors.post_content) {
      setErrors((previous) => ({ ...previous, post_content: '' }));
    }
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    updatePost.mutate({ uuid, payload: form }, {
      onSuccess: () => {
        router.visit(`/posts/${uuid}`);
      },
      onError: (error: unknown) => {
        setErrors(normalizeServerErrors(error));
      },
    });
  }

  if (isPending) {
    return (
      <AppLayout>
        <Head title="Edit Post" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--text-muted)' }}>
          Loading post…
        </div>
      </AppLayout>
    );
  }

  if (isError || !data) {
    return (
      <AppLayout>
        <Head title="Edit Post" />
        <div className="rounded-3xl p-8" style={{ background: 'var(--bg-card)', border: '1px solid var(--border-default)', color: 'var(--accent-error)' }}>
          Unable to load the selected post.
        </div>
      </AppLayout>
    );
  }

  return (
    <AppLayout>
      <Head title={`Edit ${data.post_title}`} />
      <PostForm
        title="Edit Post"
        description="Adjust content, metadata and publication timing for the selected article."
        backHref={`/posts/${uuid}`}
        submitLabel="Save Changes"
        isSubmitting={updatePost.isPending}
        form={form}
        errors={errors}
        onChange={handleChange}
        onContentChange={handleContentChange}
        onSubmit={(event) => {
          void handleSubmit(event);
        }}
      />
    </AppLayout>
  );
}
