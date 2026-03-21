import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { ServiceCategoryFilters, PaginatedServiceCategoryResponse } from '../types';

async function fetchServiceCategories(
    filters: ServiceCategoryFilters,
): Promise<PaginatedServiceCategoryResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append('search', filters.search);
    if (filters.status) params.append('status', filters.status);
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);
    if (filters.page) params.append('page', filters.page.toString());
    if (filters.per_page) params.append('per_page', filters.per_page.toString());

    const response = await fetch(`/service-categories/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error('Failed to fetch service categories.');
    }

    return response.json() as Promise<PaginatedServiceCategoryResponse>;
}

export function useServiceCategories(filters: ServiceCategoryFilters) {
    return useQuery<PaginatedServiceCategoryResponse, Error>({
        queryKey: ['service-categories', 'list', filters],
        queryFn: () => fetchServiceCategories(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
