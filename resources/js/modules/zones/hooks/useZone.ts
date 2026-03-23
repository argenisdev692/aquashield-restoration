import { useQuery } from '@tanstack/react-query';
import type { Zone } from '../types';

async function fetchZone(uuid: string): Promise<Zone> {
    const response = await fetch(`/zones/data/admin/${uuid}`);

    if (!response.ok) {
        throw new Error('Zone not found.');
    }

    return response.json() as Promise<Zone>;
}

export function useZone(uuid: string) {
    return useQuery<Zone, Error>({
        queryKey: ['zones', 'detail', uuid],
        queryFn:  () => fetchZone(uuid),
        staleTime: 1000 * 60 * 5,
        enabled:   Boolean(uuid),
    });
}
