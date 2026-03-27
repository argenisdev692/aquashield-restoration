import * as React from 'react';
import { Head, router } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import PostForm from '@/pages/posts/components/PostForm';
import { usePostMutations } from '@/modules/posts/hooks/usePostMutations';
import type { CreatePostPayload } from '@/modules/posts/types';

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

export default function PostCreatePage(): React.JSX.Element {
  const [form, setForm] = React.useState<CreatePostPayload>({
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

  const [aiTopic, setAiTopic] = React.useState<string>('');
  const [aiNiche, setAiNiche] = React.useState<string>('');
  const [aiWordCount, setAiWordCount] = React.useState<number>(1200);

  const [errors, setErrors] = React.useState<Record<string, string>>({});
  const { createPost, generatePostContent } = usePostMutations();

  React.useEffect(() => {
    if (form.post_title && aiTopic === '') {
      setAiTopic(form.post_title);
    }
  }, [form.post_title, aiTopic]);

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

  function handleGenerate(): void {
    generatePostContent.mutate(
      { topic: aiTopic, niche: aiNiche, word_count: aiWordCount },
      {
        onSuccess: (result) => {
          setForm((previous) => ({
            ...previous,
            post_content: result.post_content,
            post_title_slug: result.post_title_slug,
            post_excerpt: result.post_excerpt,
            meta_title: result.meta_title,
            meta_description: result.meta_description,
            meta_keywords: result.meta_keywords,
          }));
        },
      },
    );
  }

  async function handleSubmit(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();

    createPost.mutate(form, {
      onSuccess: (post) => {
        router.visit(`/posts/${post.uuid}`);
      },
      onError: (error: unknown) => {
        setErrors(normalizeServerErrors(error));
      },
    });
  }

  return (
    <AppLayout>
      <Head title="New Post" />
      <PostForm
        title="New Post"
        description="Write and configure a new article for your blog."
        backHref="/posts"
        submitLabel="Publish Post"
        isSubmitting={createPost.isPending}
        form={form}
        errors={errors}
        onChange={handleChange}
        onContentChange={handleContentChange}
        onSubmit={(event) => {
          void handleSubmit(event);
        }}
        aiGenerate={{
          topic: aiTopic,
          niche: aiNiche,
          wordCount: aiWordCount,
          isGenerating: generatePostContent.isPending,
          onTopicChange: setAiTopic,
          onNicheChange: setAiNiche,
          onWordCountChange: setAiWordCount,
          onGenerate: handleGenerate,
        }}
      />
    </AppLayout>
  );
}
