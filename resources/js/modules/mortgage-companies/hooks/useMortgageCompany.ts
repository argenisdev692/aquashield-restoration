import { useQuery } from '@tanstack/react-query';
import type { MortgageCompanyDetail } from '@/types/api';

async function fetchMortgageCompany(uuid: string): Promise<MortgageCompanyDetail> {
  const response = await fetch(`/mortgage-companies/data/admin/${uuid}`);
  if (!response.ok) throw new Error('Failed to fetch mortgage company');
  return response.json();
}

export function useMortgageCompany(uuid: string) {
  return useQuery<MortgageCompanyDetail, Error>({
    queryKey: ['mortgage-companies', uuid],
    queryFn: () => fetchMortgageCompany(uuid),
    staleTime: 1000 * 60 * 5,
  });
}
