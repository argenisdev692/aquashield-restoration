import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { CreateBlogCategoryPayload, UpdateBlogCategoryPayload } from '@/modules/blog-categories/types';

export function useBlogCategoryMutations() {
  const queryClient = useQueryClient();

  const createBlogCategory = useMutation({
    mutationFn: (payload: CreateBlogCategoryPayload) => axios.post('/blog-categories/data/admin', payload),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['blog-categories'] });
    },
  });

  const updateBlogCategory = useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: UpdateBlogCategoryPayload }) =>
      axios.put(`/blog-categories/data/admin/${uuid}`, payload),
    onSuccess: (_, variables) => {
      queryClient.invalidateQueries({ queryKey: ['blog-categories'] });
      queryClient.invalidateQueries({ queryKey: ['blog-categories', variables.uuid] });
    },
  });

  const deleteBlogCategory = useMutation({
    mutationFn: (uuid: string) => axios.delete(`/blog-categories/data/admin/${uuid}`),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['blog-categories'] });
    },
  });

  const restoreBlogCategory = useMutation({
    mutationFn: (uuid: string) => axios.patch(`/blog-categories/data/admin/${uuid}/restore`),
    onSuccess: (_, uuid) => {
      queryClient.invalidateQueries({ queryKey: ['blog-categories'] });
      queryClient.invalidateQueries({ queryKey: ['blog-categories', uuid] });
    },
  });

  const bulkDeleteBlogCategories = useMutation({
    mutationFn: (uuids: string[]) => axios.post('/blog-categories/data/admin/bulk-delete', { uuids }),
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['blog-categories'] });
    },
  });

  return {
    createBlogCategory,
    updateBlogCategory,
    deleteBlogCategory,
    restoreBlogCategory,
    bulkDeleteBlogCategories,
  };
}
