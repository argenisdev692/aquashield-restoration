import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type {
    AllianceCompanyFilters,
    PaginatedAllianceCompanyResponse,
} from '../types';

export function useAllianceCompanies(filters: AllianceCompanyFilters) {
    return useQuery<PaginatedAllianceCompanyResponse, Error>({
        queryKey: ['alliance-companies', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedAllianceCompanyResponse>(
                '/alliance-companies/data/admin',
                {
                    params: filters,
                },
            );

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
