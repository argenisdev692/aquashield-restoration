import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type {
    ClaimStatusFilters,
    PaginatedClaimStatusResponse,
} from "../types";

async function fetchClaimStatuses(
    filters: ClaimStatusFilters,
): Promise<PaginatedClaimStatusResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page)
        params.append("per_page", filters.per_page.toString());

    const response = await fetch(
        `/claim-statuses/data/admin?${params.toString()}`,
    );

    if (!response.ok) {
        throw new Error("Failed to fetch claim statuses.");
    }

    return response.json() as Promise<PaginatedClaimStatusResponse>;
}

export function useClaimStatuses(filters: ClaimStatusFilters) {
    return useQuery<PaginatedClaimStatusResponse, Error>({
        queryKey: ["claim-statuses", "list", filters],
        queryFn: () => fetchClaimStatuses(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
