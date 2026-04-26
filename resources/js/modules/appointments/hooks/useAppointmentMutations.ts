import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { AppointmentFormData } from "../types";

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
        // ignore
    }
    return fallback;
}

export function useCreateAppointment() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, AppointmentFormData>({
        mutationFn: async (data) => {
            const response = await fetch("/appointments/data/admin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to create appointment."));
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
            router.visit("/appointments");
        },
    });
}

export function useUpdateAppointment() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: AppointmentFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/appointments/data/admin/${uuid}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to update appointment."));
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
            router.visit("/appointments");
        },
    });
}

export function useDeleteAppointment() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/appointments/data/admin/${uuid}`, {
                method: "DELETE",
                headers: {
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to delete appointment."));
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
        },
    });
}

export function useRestoreAppointment() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/appointments/data/admin/${uuid}/restore`, {
                method: "PATCH",
                headers: {
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to restore appointment."));
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
        },
    });
}

export function useBulkDeleteAppointments() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch("/appointments/data/admin/bulk-delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                    "X-CSRF-TOKEN": getCsrfToken(),
                    Accept: "application/json",
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error(await readErrorMessage(response, "Failed to bulk delete appointments."));
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["appointments"] });
        },
    });
}
