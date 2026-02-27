import { useQuery, keepPreviousData } from '@tanstack/react-query';
import axios from 'axios';
import type {
  PaginatedResponse,
  CompanyDataListItem,
  CompanyDataFilters,
  CompanyDataDetail,
} from '@/types/api';

async function fetchCompanyData(
  filters: CompanyDataFilters
): Promise<PaginatedResponse<CompanyDataListItem>> {
  const params = new URLSearchParams(filters as Record<string, string>);
  const { data } = await axios.get<PaginatedResponse<CompanyDataListItem>>(
    `/api/company-data?${params.toString()}`
  );
  return data;
}

export function useCompanyData(filters: CompanyDataFilters) {
  return useQuery<PaginatedResponse<CompanyDataListItem>, Error>({
    queryKey: ['company-data', 'list', filters],
    queryFn: () => fetchCompanyData(filters),
    placeholderData: keepPreviousData,
    staleTime: 1000 * 60 * 2,
  });
}

async function fetchSingleCompanyData(uuid: string): Promise<CompanyDataDetail> {
  const { data } = await axios.get<{ data: CompanyDataDetail }>(`/api/company-data/${uuid}`);
  return data.data;
}

export function useSingleCompanyData(uuid: string, options?: { enabled?: boolean }) {
  return useQuery<CompanyDataDetail, Error>({
    queryKey: ['company-data', 'detail', uuid],
    queryFn: () => fetchSingleCompanyData(uuid),
    enabled: options?.enabled !== false && !!uuid,
    staleTime: 1000 * 60 * 5,
  });
}
