import axios from 'axios';
import { useQuery } from '@tanstack/react-query';
import type { Claim } from '../types';

interface ClaimDetailResponse {
    data: Claim;
}

export function useClaim(uuid: string | null) {
    return useQuery<Claim, Error>({
        queryKey: ['claims', 'detail', uuid],
        queryFn: async () => {
            const { data } = await axios.get<ClaimDetailResponse>(
                `/claims/data/admin/${uuid}`,
            );
            return data.data;
        },
        enabled: uuid !== null && uuid.length > 0,
        staleTime: 1000 * 60 * 2,
    });
}
