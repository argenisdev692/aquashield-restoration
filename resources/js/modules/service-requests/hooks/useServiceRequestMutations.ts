import { router } from "@inertiajs/react";
import { useMutation, useQueryClient } from "@tanstack/react-query";
import axios from "axios";
import type { ServiceRequestFormData } from "../types";

export function useCreateServiceRequest() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, ServiceRequestFormData>({
        mutationFn: async (data) => {
            const response = await axios.post<{ uuid: string; message: string }>("/service-requests/data/admin", data);

            return response.data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["service-requests"] });
            router.visit("/service-requests");
        },
    });
}

export function useUpdateServiceRequest() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: ServiceRequestFormData }>({
        mutationFn: async ({ uuid, data }) => {
            await axios.put(`/service-requests/data/admin/${uuid}`, data);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["service-requests"] });
            router.visit("/service-requests");
        },
    });
}

export function useDeleteServiceRequest() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            await axios.delete(`/service-requests/data/admin/${uuid}`);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["service-requests"] });
        },
    });
}

export function useRestoreServiceRequest() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            await axios.patch(`/service-requests/data/admin/${uuid}/restore`);
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["service-requests"] });
        },
    });
}

export function useBulkDeleteServiceRequests() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            await axios.post("/service-requests/data/admin/bulk-delete", { uuids });
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ["service-requests"] });
        },
    });
}
