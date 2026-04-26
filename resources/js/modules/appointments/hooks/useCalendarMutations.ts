import { useMutation, useQueryClient } from "@tanstack/react-query";

function getCsrfToken(): string {
    const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    return meta?.content ?? "";
}

async function readErrorMessage(response: Response, fallback: string): Promise<string> {
    try {
        const body = (await response.json()) as { message?: string };

        if (typeof body.message === "string" && body.message.length > 0) {
            return body.message;
        }
    } catch {
        // ignore JSON parse errors
    }

    return fallback;
}

export interface RescheduleAppointmentPayload {
    uuid: string;
    inspection_date: string;
    inspection_time: string;
}

export function useRescheduleAppointment() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, RescheduleAppointmentPayload>({
        mutationFn: async ({ uuid, inspection_date, inspection_time }) => {
            const response = await fetch(`/appointments/data/admin/${uuid}/reschedule`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: JSON.stringify({ inspection_date, inspection_time }),
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to reschedule appointment."));
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
        },
    });
}

export interface UpdateStatusPayload {
    uuid: string;
    inspection_status: string;
}

export function useUpdateAppointmentStatus() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, UpdateStatusPayload>({
        mutationFn: async ({ uuid, inspection_status }) => {
            const response = await fetch(`/appointments/data/admin/${uuid}/status`, {
                method: "PATCH",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: JSON.stringify({ inspection_status }),
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to update appointment status."));
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
        },
    });
}
