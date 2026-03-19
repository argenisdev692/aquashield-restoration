import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type { EmailDataFilters, PaginatedEmailDataResponse } from "../types";

async function fetchEmailData(
    filters: EmailDataFilters,
): Promise<PaginatedEmailDataResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.type) params.append("type", filters.type);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/email-data/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch email data records.");
    }

    return response.json() as Promise<PaginatedEmailDataResponse>;
}

export function useEmailData(filters: EmailDataFilters) {
    return useQuery<PaginatedEmailDataResponse, Error>({
        queryKey: ["email-data", "list", filters],
        queryFn: () => fetchEmailData(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
