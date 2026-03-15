import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import type { CauseOfLossFormData } from "../types";

export function useCreateCauseOfLoss() {
    const queryClient = useQueryClient();

    return useMutation<
        { uuid: string; message: string },
        Error,
        CauseOfLossFormData
    >({
        mutationFn: async (data) => {
            const response = await fetch("/cause-of-losses/data/admin", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to create cause of loss.");
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["cause-of-losses"] });
            router.visit("/cause-of-losses");
        },
    });
}

export function useUpdateCauseOfLoss() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: CauseOfLossFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/cause-of-losses/data/admin/${uuid}`, {
                method: "PUT",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error("Failed to update cause of loss.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["cause-of-losses"] });
            router.visit("/cause-of-losses");
        },
    });
}

export function useDeleteCauseOfLoss() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/cause-of-losses/data/admin/${uuid}`, {
                method: "DELETE",
            });

            if (!response.ok) {
                throw new Error("Failed to delete cause of loss.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["cause-of-losses"] });
        },
    });
}

export function useRestoreCauseOfLoss() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(
                `/cause-of-losses/data/admin/${uuid}/restore`,
                {
                    method: "PATCH",
                },
            );

            if (!response.ok) {
                throw new Error("Failed to restore cause of loss.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["cause-of-losses"] });
        },
    });
}

export function useBulkDeleteCauseOfLosses() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch("/cause-of-losses/data/admin/bulk-delete", {
                method: "POST",
                headers: {
                    "Content-Type": "application/json",
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error("Failed to bulk delete cause of losses.");
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["cause-of-losses"] });
        },
    });
}
