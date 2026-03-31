import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { ScopeSheet, ScopeSheetFilters, PaginatedScopeSheetResponse } from '../types';

export function useScopeSheet(uuid: string | null) {
    return useQuery<ScopeSheet, Error>({
        queryKey: ['scope-sheets', 'detail', uuid],
        queryFn: async () => {
            const { data } = await axios.get<ScopeSheet>(
                `/scope-sheets/data/admin/${uuid}`,
            );
            return data;
        },
        enabled: uuid !== null && uuid.length > 0,
        staleTime: 1000 * 60 * 2,
    });
}

export function useScopeSheetByClaim(claimId: number | null) {
    return useQuery<PaginatedScopeSheetResponse, Error>({
        queryKey: ['scope-sheets', 'by-claim', claimId],
        queryFn: async () => {
            const params: ScopeSheetFilters = { claim_id: claimId!, per_page: 1, page: 1 };
            const { data } = await axios.get<PaginatedScopeSheetResponse>(
                '/scope-sheets/data/admin',
                { params },
            );
            return data;
        },
        enabled: claimId !== null && claimId > 0,
        staleTime: 1000 * 60 * 2,
    });
}
