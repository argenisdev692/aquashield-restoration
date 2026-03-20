import { keepPreviousData, useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { PaginatedResponse } from '@/types/api';
import type { InsuranceCompany, InsuranceCompanyFilters } from '../types';

export const useInsuranceCompanies = (filters: InsuranceCompanyFilters = {}) => {
    return useQuery<PaginatedResponse<InsuranceCompany>, Error>({
        queryKey: ['insurance-companies', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedResponse<InsuranceCompany>>('/insurance-companies/data/admin', {
                params: filters,
            });

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
};
