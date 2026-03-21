import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { PaginatedProjectTypeResponse, ProjectTypeFilters, ServiceCategoryOption } from '../types';

async function fetchProjectTypes(filters: ProjectTypeFilters): Promise<PaginatedProjectTypeResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append('search', filters.search);
    if (filters.status) params.append('status', filters.status);
    if (filters.service_category_uuid) params.append('service_category_uuid', filters.service_category_uuid);
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);
    if (filters.page) params.append('page', filters.page.toString());
    if (filters.per_page) params.append('per_page', filters.per_page.toString());

    const response = await fetch(`/project-types/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error('Failed to fetch project types.');
    }

    return response.json() as Promise<PaginatedProjectTypeResponse>;
}

async function fetchServiceCategoryOptions(): Promise<ServiceCategoryOption[]> {
    const response = await fetch('/project-types/data/admin/service-categories');

    if (!response.ok) {
        throw new Error('Failed to fetch service categories.');
    }

    const json = (await response.json()) as { data: ServiceCategoryOption[] };

    return json.data;
}

export function useProjectTypes(filters: ProjectTypeFilters) {
    return useQuery<PaginatedProjectTypeResponse, Error>({
        queryKey: ['project-types', 'list', filters],
        queryFn: () => fetchProjectTypes(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}

export function useServiceCategoryOptions() {
    return useQuery<ServiceCategoryOption[], Error>({
        queryKey: ['service-categories', 'options'],
        queryFn: fetchServiceCategoryOptions,
        staleTime: 1000 * 60 * 5,
    });
}
