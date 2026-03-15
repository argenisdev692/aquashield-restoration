import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type {
    ContactSupportFilters,
    PaginatedContactSupportResponse,
} from "../types";

async function fetchContactSupports(
    filters: ContactSupportFilters,
): Promise<PaginatedContactSupportResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.read_state) params.append("read_state", filters.read_state);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/contact-supports/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch contact support records.");
    }

    return response.json() as Promise<PaginatedContactSupportResponse>;
}

export function useContactSupports(filters: ContactSupportFilters) {
    return useQuery<PaginatedContactSupportResponse, Error>({
        queryKey: ["contact-supports", "list", filters],
        queryFn: () => fetchContactSupports(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
