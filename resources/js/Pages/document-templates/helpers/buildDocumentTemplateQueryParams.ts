import type { DocumentTemplateFilters } from '@/modules/document-templates/types';

export function buildDocumentTemplateQueryParams(
    filters: DocumentTemplateFilters,
): URLSearchParams {
    const params = new URLSearchParams();
    if (filters.search) params.append('search', filters.search);
    if (filters.template_type) params.append('template_type', filters.template_type);
    if (filters.date_from) params.append('date_from', filters.date_from);
    if (filters.date_to) params.append('date_to', filters.date_to);
    if (filters.page) params.append('page', String(filters.page));
    if (filters.per_page) params.append('per_page', String(filters.per_page));
    return params;
}
