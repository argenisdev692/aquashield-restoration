import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type { DocumentTemplateFilters, PaginatedDocumentTemplateResponse } from '../types';

export const documentTemplateKeys = {
    all: ['document-templates'] as const,
    list: (filters: DocumentTemplateFilters) =>
        [...documentTemplateKeys.all, 'list', filters] as const,
    detail: (uuid: string) =>
        [...documentTemplateKeys.all, 'detail', uuid] as const,
};

export function useDocumentTemplates(
    filters: DocumentTemplateFilters,
): ReturnType<typeof useQuery<PaginatedDocumentTemplateResponse>> {
    return useQuery<PaginatedDocumentTemplateResponse>({
        queryKey: documentTemplateKeys.list(filters),
        queryFn: async () => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.template_type) params.append('template_type', filters.template_type);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.page) params.append('page', String(filters.page));
            if (filters.per_page) params.append('per_page', String(filters.per_page));

            const { data } = await axios.get<PaginatedDocumentTemplateResponse>(
                `/document-templates/data/admin?${params.toString()}`,
            );
            return data;
        },
        placeholderData: (prev) => prev,
    });
}
