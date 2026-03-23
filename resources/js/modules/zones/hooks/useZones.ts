import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { PaginatedZoneResponse, ZoneFilters } from '../types';

async function fetchZones(filters: ZoneFilters): Promise<PaginatedZoneResponse> {
    const params = new URLSearchParams();

    if (filters.search)    params.append('search',    filters.search);
    if (filters.zone_type) params.append('zone_type', filters.zone_type);
    if (filters.status)    params.append('status',    filters.status);
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to)   params.append('date_to',   filters.date_to);
    if (filters.page)      params.append('page',      filters.page.toString());
    if (filters.per_page)  params.append('per_page',  filters.per_page.toString());

    const response = await fetch(`/zones/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error('Failed to fetch zones.');
    }

    return response.json() as Promise<PaginatedZoneResponse>;
}

export function useZones(filters: ZoneFilters) {
    return useQuery<PaginatedZoneResponse, Error>({
        queryKey: ['zones', 'list', filters],
        queryFn:  () => fetchZones(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
