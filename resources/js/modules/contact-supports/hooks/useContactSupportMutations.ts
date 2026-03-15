import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { ContactSupportFormData } from "../types";

export function useCreateContactSupport() {
    const queryClient = useQueryClient();

    return useMutation<
        { uuid: string; message: string },
        Error,
        ContactSupportFormData
    >({
        mutationFn: async (data) => {
            const response = await fetch("/contact-supports/data/admin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to create contact support record.");
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["contact-supports"] });
            router.visit("/contact-supports");
        },
    });
}

export function useUpdateContactSupport() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: ContactSupportFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/contact-supports/data/admin/${uuid}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to update contact support record.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["contact-supports"] });
            router.visit("/contact-supports");
        },
    });
}

export function useDeleteContactSupport() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/contact-supports/data/admin/${uuid}`, {
                method: "DELETE",
            });

            if (!response.ok) {
                throw new Error("Failed to delete contact support record.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["contact-supports"] });
        },
    });
}

export function useRestoreContactSupport() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/contact-supports/data/admin/${uuid}/restore`, {
                method: "PATCH",
            });

            if (!response.ok) {
                throw new Error("Failed to restore contact support record.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["contact-supports"] });
        },
    });
}

export function useBulkDeleteContactSupports() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch("/contact-supports/data/admin/bulk-delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error("Failed to bulk delete contact support records.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["contact-supports"] });
        },
    });
}
