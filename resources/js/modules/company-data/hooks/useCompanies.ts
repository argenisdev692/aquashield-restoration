import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { CompanyDataFilters, CompanyDataPaginatedResponse } from '@/modules/company-data/types';

/**
 * useCompanies — Fetches a paginated list of company profiles.
 */
export const useCompanies = (filters: CompanyDataFilters) => {
  return useQuery({
    queryKey: ['company-data', 'list', filters],
    queryFn: async () => {
      const { data } = await axios.get<CompanyDataPaginatedResponse>('/company-data/data/admin', {
        params: filters
      });
      return data;
    },
    placeholderData: keepPreviousData,
  });
};
