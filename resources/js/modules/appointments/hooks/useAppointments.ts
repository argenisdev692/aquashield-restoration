import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type {
    AppointmentFilters,
    PaginatedAppointmentResponse,
} from "../types";

async function fetchAppointments(
    filters: AppointmentFilters,
): Promise<PaginatedAppointmentResponse> {
    const params = new URLSearchParams();

    if (filters.search) params.append("search", filters.search);
    if (filters.status) params.append("status", filters.status);
    if (filters.inspection_status) params.append("inspection_status", filters.inspection_status);
    if (filters.status_lead) params.append("status_lead", filters.status_lead);
    if (filters.date_from) params.append("date_from", filters.date_from);
    if (filters.date_to) params.append("date_to", filters.date_to);
    if (filters.page) params.append("page", filters.page.toString());
    if (filters.per_page) params.append("per_page", filters.per_page.toString());

    const response = await fetch(`/appointments/data/admin?${params.toString()}`);

    if (!response.ok) {
        throw new Error("Failed to fetch appointments.");
    }

    return response.json() as Promise<PaginatedAppointmentResponse>;
}

export function useAppointments(filters: AppointmentFilters) {
    return useQuery<PaginatedAppointmentResponse, Error>({
        queryKey: ["appointments", "list", filters],
        queryFn: () => fetchAppointments(filters),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 60 * 2,
    });
}
