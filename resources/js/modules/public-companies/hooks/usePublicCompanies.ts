import { useQuery, keepPreviousData } from '@tanstack/react-query';
import axios from 'axios';
import { PublicCompany, PublicCompanyFilters } from '../types';
import { PaginatedResponse } from '@/types/api';

export const usePublicCompanies = (filters: PublicCompanyFilters = {}) => {
    return useQuery({
        queryKey: ['public-companies', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedResponse<PublicCompany>>('/public-companies/data', {
                params: filters,
            });
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
};
