import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type {
    CauseOfLossFilters,
    PaginatedCauseOfLossResponse,
} from "../types";

async function fetchCauseOfLosses(
    filters: CauseOfLossFilters,
): Promise<PaginatedCauseOfLossResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.severity) params.append("severity", filters.severity);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/cause-of-losses/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch cause of losses.");
    }

    return response.json() as Promise<PaginatedCauseOfLossResponse>;
}

export function useCauseOfLosses(filters: CauseOfLossFilters) {
    return useQuery<PaginatedCauseOfLossResponse, Error>({
        queryKey: ["cause-of-losses", "list", filters],
        queryFn: () => fetchCauseOfLosses(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
