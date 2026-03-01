import { useQuery, keepPreviousData } from '@tanstack/react-query';
import axios from 'axios';
import { AllianceCompany, AllianceCompanyFilters } from '../types';
import { PaginatedResponse } from '@/types/api';

export const useAllianceCompanies = (filters: AllianceCompanyFilters = {}) => {
    return useQuery({
        queryKey: ['alliance-companies', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedResponse<AllianceCompany>>('/alliance-companies/data', {
                params: filters,
            });
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
};
