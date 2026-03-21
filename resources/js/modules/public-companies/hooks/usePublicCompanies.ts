import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { PaginatedResponse } from '@/types/api';
import type { PublicCompany, PublicCompanyFilters } from '../types';

export const usePublicCompanies = (filters: PublicCompanyFilters = {}) => {
    return useQuery<PaginatedResponse<PublicCompany>, Error>({
        queryKey: ['public-companies', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedResponse<PublicCompany>>('/public-companies/data/admin', {
                params: filters,
            });

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
};
