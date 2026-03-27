import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { CampaignFilters, CampaignListItem } from '@/modules/ai-campaigns/types';

export interface PaginatedCampaigns {
  data: CampaignListItem[];
  meta: {
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
  };
}

export function useCampaigns(filters: CampaignFilters = {}) {
  return useQuery<PaginatedCampaigns, Error>({
    queryKey: ['ai-campaigns', filters],
    queryFn: async () => {
      const { data } = await axios.get<PaginatedCampaigns>('/ai-campaigns/data/admin', {
        params: filters,
      });
      return data;
    },
    placeholderData: keepPreviousData,
    staleTime: 1000 * 60 * 2,
  });
}
