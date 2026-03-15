import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type {
    PaginatedTypeDamageResponse,
    TypeDamageFilters,
} from "../types";

async function fetchTypeDamages(
    filters: TypeDamageFilters,
): Promise<PaginatedTypeDamageResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.severity) params.append("severity", filters.severity);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/type-damages/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch type damages.");
    }

    return response.json() as Promise<PaginatedTypeDamageResponse>;
}

export function useTypeDamages(filters: TypeDamageFilters) {
    return useQuery<PaginatedTypeDamageResponse, Error>({
        queryKey: ["type-damages", "list", filters],
        queryFn: () => fetchTypeDamages(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
