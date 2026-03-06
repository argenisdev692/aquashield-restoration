import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type { CreatePostPayload, PostDetail, UpdatePostPayload } from '@/modules/posts/types';

function getErrorMessage(error: AxiosError | Error | unknown, fallback: string): string {
  if (axios.isAxiosError(error)) {
    const message = error.response?.data as { message?: string } | undefined;

    if (typeof message?.message === 'string' && message.message.length > 0) {
      return message.message;
    }
  }

  if (error instanceof Error && error.message.length > 0) {
    return error.message;
  }

  return fallback;
}

export function usePostMutations() {
  const queryClient = useQueryClient();

  const createPost = useMutation({
    mutationFn: async (payload: CreatePostPayload) => {
      const { data } = await axios.post<{ data: PostDetail }>('/posts/data/admin', payload);
      return data.data;
    },
    onSuccess: () => {
      sileo.success({ title: 'Post created successfully' });
      queryClient.invalidateQueries({ queryKey: ['posts'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to create post') });
    },
  });

  const updatePost = useMutation({
    mutationFn: async ({ uuid, payload }: { uuid: string; payload: UpdatePostPayload }) => {
      const { data } = await axios.put<{ data: PostDetail }>(`/posts/data/admin/${uuid}`, payload);
      return data.data;
    },
    onSuccess: (_, variables) => {
      sileo.success({ title: 'Post updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', variables.uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update post') });
    },
  });

  const deletePost = useMutation({
    mutationFn: (uuid: string) => axios.delete(`/posts/data/admin/${uuid}`),
    onSuccess: () => {
      sileo.success({ title: 'Post deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['posts'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete post') });
    },
  });

  const restorePost = useMutation({
    mutationFn: (uuid: string) => axios.patch(`/posts/data/admin/${uuid}/restore`),
    onSuccess: (_, uuid) => {
      sileo.success({ title: 'Post restored successfully' });
      queryClient.invalidateQueries({ queryKey: ['posts'] });
      queryClient.invalidateQueries({ queryKey: ['posts', uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to restore post') });
    },
  });

  const bulkDeletePosts = useMutation({
    mutationFn: (uuids: string[]) => axios.post('/posts/data/admin/bulk-delete', { uuids }),
    onSuccess: () => {
      sileo.success({ title: 'Selected posts deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['posts'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete selected posts') });
    },
  });

  return {
    createPost,
    updatePost,
    deletePost,
    restorePost,
    bulkDeletePosts,
  };
}
