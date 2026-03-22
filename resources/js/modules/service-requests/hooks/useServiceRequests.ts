import { keepPreviousData, useQuery } from "@tanstack/react-query";
import axios from "axios";
import type { PaginatedServiceRequestResponse, ServiceRequestFilters } from "../types";

async function fetchServiceRequests(filters: ServiceRequestFilters): Promise<PaginatedServiceRequestResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.status) params.append("status", filters.status);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/service-requests/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch service requests.");
    }

    return response.json() as Promise<PaginatedServiceRequestResponse>;
}

export function useServiceRequests(filters: ServiceRequestFilters) {
    return useQuery<PaginatedServiceRequestResponse, Error>({
        queryKey: ["service-requests", "list", filters],
        queryFn: async () => {
            const { data } = await axios.get<PaginatedServiceRequestResponse>("/service-requests/data/admin", {
                params: filters,
            });

            return data;
        },
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
