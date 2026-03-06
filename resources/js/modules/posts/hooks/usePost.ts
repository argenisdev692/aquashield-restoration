import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { PostDetail } from '@/modules/posts/types';

interface PostShowResponse {
  data: PostDetail;
}

export function usePost(uuid: string) {
  return useQuery<PostDetail, Error>({
    queryKey: ['posts', uuid],
    queryFn: async () => {
      const { data } = await axios.get<PostShowResponse>(`/posts/data/admin/${uuid}`);

      return data.data;
    },
    enabled: uuid.length > 0,
  });
}
