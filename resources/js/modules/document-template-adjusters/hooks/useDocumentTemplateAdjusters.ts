import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type {
    DocumentTemplateAdjusterFilters,
    PaginatedDocumentTemplateAdjusterResponse,
} from '../types';

export const documentTemplateAdjusterKeys = {
    all: ['document-template-adjusters'] as const,
    list: (filters: DocumentTemplateAdjusterFilters) =>
        [...documentTemplateAdjusterKeys.all, 'list', filters] as const,
    detail: (uuid: string) =>
        [...documentTemplateAdjusterKeys.all, 'detail', uuid] as const,
};

export function useDocumentTemplateAdjusters(
    filters: DocumentTemplateAdjusterFilters,
): ReturnType<typeof useQuery<PaginatedDocumentTemplateAdjusterResponse>> {
    return useQuery<PaginatedDocumentTemplateAdjusterResponse>({
        queryKey: documentTemplateAdjusterKeys.list(filters),
        queryFn: async () => {
            const params = new URLSearchParams();
            if (filters.search) params.append('search', filters.search);
            if (filters.date_from) params.append('date_from', filters.date_from);
            if (filters.date_to) params.append('date_to', filters.date_to);
            if (filters.public_adjuster_id) params.append('public_adjuster_id', String(filters.public_adjuster_id));
            if (filters.template_type_adjuster) params.append('template_type_adjuster', filters.template_type_adjuster);
            if (filters.page) params.append('page', String(filters.page));
            if (filters.per_page) params.append('per_page', String(filters.per_page));

            const { data } = await axios.get<PaginatedDocumentTemplateAdjusterResponse>(
                `/document-template-adjusters/data/admin?${params.toString()}`,
            );
            return data;
        },
        placeholderData: (prev) => prev,
    });
}

export function useDocumentTemplateAdjuster(
    uuid: string,
): ReturnType<typeof useQuery<import('../types').DocumentTemplateAdjuster>> {
    return useQuery<import('../types').DocumentTemplateAdjuster>({
        queryKey: documentTemplateAdjusterKeys.detail(uuid),
        queryFn: async () => {
            const { data } = await axios.get<import('../types').DocumentTemplateAdjuster>(
                `/document-template-adjusters/data/admin/${uuid}`,
            );
            return data;
        },
        enabled: uuid !== '',
    });
}
