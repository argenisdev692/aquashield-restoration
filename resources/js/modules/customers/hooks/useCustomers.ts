import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { CustomerFilters, PaginatedCustomerResponse } from '../types';

export function useCustomers(filters: CustomerFilters) {
    return useQuery<PaginatedCustomerResponse, Error>({
        queryKey: ['customers', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedCustomerResponse>(
                '/customers/data/admin',
                { params: filters },
            );

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
