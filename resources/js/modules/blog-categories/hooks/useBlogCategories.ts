import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { PaginatedResponse } from '@/types/api';
import type { BlogCategoryFilters, BlogCategoryListItem } from '@/modules/blog-categories/types';

export function useBlogCategories(filters: BlogCategoryFilters = {}) {
  return useQuery<PaginatedResponse<BlogCategoryListItem>, Error>({
    queryKey: ['blog-categories', filters],
    queryFn: async () => {
      const { data } = await axios.get<PaginatedResponse<BlogCategoryListItem>>('/blog-categories/data/admin', {
        params: filters,
      });

      return data;
    },
    placeholderData: keepPreviousData,
    staleTime: 1000 * 60 * 2,
  });
}
