import { useQuery, keepPreviousData } from '@tanstack/react-query';
import axios from 'axios';
import { InsuranceCompany, InsuranceCompanyFilters } from '../types';
import { PaginatedResponse } from '@/types/api';

export const useInsuranceCompanies = (filters: InsuranceCompanyFilters = {}) => {
    return useQuery({
        queryKey: ['insurance-companies', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedResponse<InsuranceCompany>>('/insurance-companies/data', {
                params: filters,
            });
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
};
