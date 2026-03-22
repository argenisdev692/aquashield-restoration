import { useQuery } from "@tanstack/react-query";
import axios from "axios";
import type {
    DocumentTemplateAllianceFilters,
    PaginatedDocumentTemplateAllianceResponse,
} from "../types";

export const documentTemplateAllianceKeys = {
    all: ["document-template-alliances"] as const,
    list: (filters: DocumentTemplateAllianceFilters) =>
        [...documentTemplateAllianceKeys.all, "list", filters] as const,
    detail: (uuid: string) =>
        [...documentTemplateAllianceKeys.all, "detail", uuid] as const,
};

export function useDocumentTemplateAlliances(
    filters: DocumentTemplateAllianceFilters,
): ReturnType<typeof useQuery<PaginatedDocumentTemplateAllianceResponse>> {
    return useQuery<PaginatedDocumentTemplateAllianceResponse>({
        queryKey: documentTemplateAllianceKeys.list(filters),
        queryFn: async () => {
            const params = new URLSearchParams();

            if (filters.search) params.append("search", filters.search);
            if (filters.date_from) params.append("date_from", filters.date_from);
            if (filters.date_to) params.append("date_to", filters.date_to);
            if (filters.alliance_company_id) params.append("alliance_company_id", String(filters.alliance_company_id));
            if (filters.template_type_alliance) params.append("template_type_alliance", filters.template_type_alliance);
            if (filters.page) params.append("page", String(filters.page));
            if (filters.per_page) params.append("per_page", String(filters.per_page));

            const { data } = await axios.get<PaginatedDocumentTemplateAllianceResponse>(
                `/document-template-alliances/data/admin?${params.toString()}`,
            );

            return data;
        },
        placeholderData: (prev) => prev,
    });
}
