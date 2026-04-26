import { keepPreviousData, useQuery } from "@tanstack/react-query";
import type { AppointmentCalendarEvent } from "../types";

interface CalendarRange {
    start?: string;
    end?: string;
}

async function fetchCalendarEvents(range: CalendarRange): Promise<AppointmentCalendarEvent[]> {
    const params = new URLSearchParams();

    if (range.start) {
        params.append("start", range.start);
    }
    if (range.end) {
        params.append("end", range.end);
    }

    const response = await fetch(`/appointments/data/admin/calendar/events?${params.toString()}`, {
        headers: { Accept: "application/json" },
    });

    if (!response.ok) {
        throw new Error("Failed to fetch calendar events.");
    }

    return response.json() as Promise<AppointmentCalendarEvent[]>;
}

export function useCalendarEvents(range: CalendarRange) {
    return useQuery<AppointmentCalendarEvent[], Error>({
        queryKey: ["appointments", "calendar", range.start ?? null, range.end ?? null],
        queryFn: () => fetchCalendarEvents(range),
        placeholderData: keepPreviousData,
        staleTime: 1000 * 30,
    });
}
