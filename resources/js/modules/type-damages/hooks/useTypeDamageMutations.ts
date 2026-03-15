import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { TypeDamageFormData } from "../types";

export function useCreateTypeDamage() {
    const queryClient = useQueryClient();

    return useMutation<
        { uuid: string; message: string },
        Error,
        TypeDamageFormData
    >({
        mutationFn: async (data) => {
            const response = await fetch("/type-damages/data/admin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to create type damage.");
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["type-damages"] });
            router.visit("/type-damages");
        },
    });
}

export function useUpdateTypeDamage() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: TypeDamageFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/type-damages/data/admin/${uuid}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to update type damage.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["type-damages"] });
            router.visit("/type-damages");
        },
    });
}

export function useDeleteTypeDamage() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/type-damages/data/admin/${uuid}`, {
                method: "DELETE",
            });

            if (!response.ok) {
                throw new Error("Failed to delete type damage.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["type-damages"] });
        },
    });
}

export function useRestoreTypeDamage() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(
                `/type-damages/data/admin/${uuid}/restore`,
                {
                    method: "PATCH",
                },
            );

            if (!response.ok) {
                throw new Error("Failed to restore type damage.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["type-damages"] });
        },
    });
}

export function useBulkDeleteTypeDamages() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch("/type-damages/data/admin/bulk-delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error("Failed to bulk delete type damages.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["type-damages"] });
        },
    });
}
