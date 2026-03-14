import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { CompanyDataDetail } from '@/modules/company-data/types';

/**
 * useSingleCompanyData — Fetches a single company profile by UUID or for the current user.
 */
export const useSingleCompanyData = (uuid?: string) => {
  return useQuery({
    queryKey: ['company-data', 'detail', uuid || 'me'],
    queryFn: async () => {
      const url = uuid ? `/company-data/data/admin/${uuid}` : '/company-data/data/me';
      const { data } = await axios.get<{ data: CompanyDataDetail }>(url);
      return data.data;
    },
  });
};

// Alias for backward compatibility if needed, but we prefer useSingleCompanyData
export const useCompanyData = useSingleCompanyData;
