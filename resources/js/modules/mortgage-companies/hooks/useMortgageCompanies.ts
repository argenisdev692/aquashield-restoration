import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type {
    MortgageCompanyFilters,
    PaginatedMortgageCompanyResponse,
} from '../types';

export function useMortgageCompanies(filters: MortgageCompanyFilters) {
    return useQuery<PaginatedMortgageCompanyResponse, Error>({
        queryKey: ['mortgage-companies', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedMortgageCompanyResponse>(
                '/mortgage-companies/data/admin',
                { params: filters },
            );

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
