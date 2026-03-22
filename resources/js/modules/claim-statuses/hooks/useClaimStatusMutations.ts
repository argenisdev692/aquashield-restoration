import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { ClaimStatusFormData } from "../types";

export function useCreateClaimStatus() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, ClaimStatusFormData>(
        {
            mutationFn: async (data) => {
                const response = await fetch("/claim-statuses/data/admin", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data),
                });

                if (!response.ok) {
                    throw new Error("Failed to create claim status.");
                }

                return response.json() as Promise<{
                    uuid: string;
                    message: string;
                }>;
            },
            onSuccess: async () => {
                await queryClient.invalidateQueries({
                    queryKey: ["claim-statuses"],
                });
                router.visit("/claim-statuses");
            },
        },
    );
}

export function useUpdateClaimStatus() {
    const queryClient = useQueryClient();

    return useMutation<
        void,
        Error,
        { uuid: string; data: ClaimStatusFormData }
    >({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(
                `/claim-statuses/data/admin/${uuid}`,
                {
                    method: "PUT",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify(data),
                },
            );

            if (!response.ok) {
                throw new Error("Failed to update claim status.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: ["claim-statuses"],
            });
            router.visit("/claim-statuses");
        },
    });
}

export function useDeleteClaimStatus() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(
                `/claim-statuses/data/admin/${uuid}`,
                { method: "DELETE" },
            );

            if (!response.ok) {
                throw new Error("Failed to delete claim status.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: ["claim-statuses"],
            });
        },
    });
}

export function useRestoreClaimStatus() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(
                `/claim-statuses/data/admin/${uuid}/restore`,
                { method: "PATCH" },
            );

            if (!response.ok) {
                throw new Error("Failed to restore claim status.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: ["claim-statuses"],
            });
        },
    });
}

export function useBulkDeleteClaimStatuses() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch(
                "/claim-statuses/data/admin/bulk-delete",
                {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ uuids }),
                },
            );

            if (!response.ok) {
                throw new Error("Failed to bulk delete claim statuses.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({
                queryKey: ["claim-statuses"],
            });
        },
    });
}
