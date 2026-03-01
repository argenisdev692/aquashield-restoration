import { useQuery, keepPreviousData } from '@tanstack/react-query';
import type { PaginatedResponse, MortgageCompanyListItem, MortgageCompanyFilters } from '@/types/api';

async function fetchMortgageCompanies(filters: MortgageCompanyFilters): Promise<PaginatedResponse<MortgageCompanyListItem>> {
  const params = new URLSearchParams();
  
  if (filters.page) params.append('page', filters.page.toString());
  if (filters.perPage) params.append('perPage', filters.perPage.toString());
  if (filters.search) params.append('search', filters.search);
  if (filters.status) params.append('status', filters.status);
  if (filters.dateFrom) params.append('dateFrom', filters.dateFrom);
  if (filters.dateTo) params.append('dateTo', filters.dateTo);

  const response = await fetch(`/mortgage-companies/data/admin?${params}`);
  if (!response.ok) throw new Error('Failed to fetch mortgage companies');
  return response.json();
}

export function useMortgageCompanies(filters: MortgageCompanyFilters) {
  return useQuery<PaginatedResponse<MortgageCompanyListItem>, Error>({
    queryKey: ['mortgage-companies', 'list', filters],
    queryFn: () => fetchMortgageCompanies(filters),
    placeholderData: keepPreviousData,
    staleTime: 1000 * 60 * 2,
  });
}
