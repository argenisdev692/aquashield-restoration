import { keepPreviousData, useQuery } from '@tanstack/react-query';
import type { PaginatedPortfolioResponse, Portfolio, PortfolioFilters, ProjectTypeOption } from '../types';

async function fetchPortfolios(filters: PortfolioFilters): Promise<PaginatedPortfolioResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append('search', filters.search);
    if (filters.status) params.append('status', filters.status);
    if (filters.project_type_uuid) params.append('project_type_uuid', filters.project_type_uuid);
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);
    if (filters.page) params.append('page', filters.page.toString());
    if (filters.per_page) params.append('per_page', filters.per_page.toString());

    const response = await fetch(`/portfolios/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error('Failed to fetch portfolios.');
    }

    return response.json() as Promise<PaginatedPortfolioResponse>;
}

async function fetchPortfolio(uuid: string): Promise<Portfolio> {
    const response = await fetch(`/portfolios/data/admin/${uuid}`);

    if (!response.ok) {
        throw new Error('Failed to fetch portfolio.');
    }

    return response.json() as Promise<Portfolio>;
}

async function fetchProjectTypeOptions(): Promise<ProjectTypeOption[]> {
    const response = await fetch('/portfolios/data/admin/project-types');

    if (!response.ok) {
        throw new Error('Failed to fetch project types.');
    }

    const json = (await response.json()) as { data: ProjectTypeOption[] };

    return json.data;
}

export function usePortfolios(filters: PortfolioFilters) {
    return useQuery<PaginatedPortfolioResponse, Error>({
        queryKey: ['portfolios', 'list', filters],
        queryFn: () => fetchPortfolios(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}

export function usePortfolio(uuid: string) {
    return useQuery<Portfolio, Error>({
        queryKey: ['portfolios', 'detail', uuid],
        queryFn: () => fetchPortfolio(uuid),
        staleTime: 1000 * 60 * 2,
    });
}

export function useProjectTypeOptions() {
    return useQuery<ProjectTypeOption[], Error>({
        queryKey: ['project-types', 'options'],
        queryFn: fetchProjectTypeOptions,
        staleTime: 1000 * 60 * 5,
    });
}
