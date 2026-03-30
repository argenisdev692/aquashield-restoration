import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { ClaimFilters, PaginatedClaimResponse } from '../types';

export function useClaims(filters: ClaimFilters) {
    return useQuery<PaginatedClaimResponse, Error>({
        queryKey: ['claims', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedClaimResponse>(
                '/claims/data/admin',
                { params: filters },
            );
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
