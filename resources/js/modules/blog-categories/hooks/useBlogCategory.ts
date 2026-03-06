import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { BlogCategoryDetail } from '@/modules/blog-categories/types';

interface BlogCategoryShowResponse {
  data: BlogCategoryDetail;
}

export function useBlogCategory(uuid: string) {
  return useQuery<BlogCategoryDetail, Error>({
    queryKey: ['blog-categories', uuid],
    queryFn: async () => {
      const { data } = await axios.get<BlogCategoryShowResponse>(`/blog-categories/data/admin/${uuid}`);

      return data.data;
    },
    enabled: uuid.length > 0,
  });
}
