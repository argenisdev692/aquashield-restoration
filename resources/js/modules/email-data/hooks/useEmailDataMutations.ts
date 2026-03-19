import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { EmailDataFormData } from "../types";

export function useCreateEmailData() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, EmailDataFormData>({
        mutationFn: async (data) => {
            const response = await fetch("/email-data/data/admin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to create email data record.");
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["email-data"] });
            router.visit("/email-data");
        },
    });
}

export function useUpdateEmailData() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: EmailDataFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/email-data/data/admin/${uuid}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to update email data record.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["email-data"] });
            router.visit("/email-data");
        },
    });
}

export function useDeleteEmailData() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/email-data/data/admin/${uuid}`, {
                method: "DELETE",
            });

            if (!response.ok) {
                throw new Error("Failed to delete email data record.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["email-data"] });
        },
    });
}

export function useRestoreEmailData() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/email-data/data/admin/${uuid}/restore`, {
                method: "PATCH",
            });

            if (!response.ok) {
                throw new Error("Failed to restore email data record.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["email-data"] });
        },
    });
}

export function useBulkDeleteEmailData() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch("/email-data/data/admin/bulk-delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error("Failed to bulk delete email data records.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["email-data"] });
        },
    });
}
