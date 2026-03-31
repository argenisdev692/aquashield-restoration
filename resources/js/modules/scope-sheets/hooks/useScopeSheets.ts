import axios from 'axios';
import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { PaginatedScopeSheetResponse, ScopeSheetFilters } from '../types';

export function useScopeSheets(filters: ScopeSheetFilters) {
    return useQuery<PaginatedScopeSheetResponse, Error>({
        queryKey: ['scope-sheets', 'list', filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedScopeSheetResponse>(
                '/scope-sheets/data/admin',
                { params: filters },
            );
            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
