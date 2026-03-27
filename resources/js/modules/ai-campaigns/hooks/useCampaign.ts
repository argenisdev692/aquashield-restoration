import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { CampaignDetail } from '@/modules/ai-campaigns/types';

export function useCampaign(uuid: string | null) {
  return useQuery<CampaignDetail, Error>({
    queryKey: ['ai-campaigns', uuid],
    queryFn: async () => {
      const { data } = await axios.get<{ data: CampaignDetail }>(`/ai-campaigns/data/admin/${uuid}`);
      return data.data;
    },
    enabled: !!uuid,
    staleTime: 1000 * 60 * 5,
  });
}
