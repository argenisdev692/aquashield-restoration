import { useMutation, useQueryClient } from "@tanstack/react-query";
import axios from "axios";
import { documentTemplateAllianceKeys } from "./useDocumentTemplateAlliances";

export function useDeleteDocumentTemplateAlliance(): ReturnType<typeof useMutation<void, Error, string>> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid: string) => {
            await axios.delete(`/document-template-alliances/data/admin/${uuid}`);
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAllianceKeys.all });
        },
    });
}

export function useBulkDeleteDocumentTemplateAlliances(): ReturnType<typeof useMutation<void, Error, string[]>> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids: string[]) => {
            await axios.post("/document-template-alliances/data/admin/bulk-delete", { uuids });
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAllianceKeys.all });
        },
    });
}

export function useCreateDocumentTemplateAlliance(): ReturnType<typeof useMutation<{ uuid: string }, Error, FormData>> {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string }, Error, FormData>({
        mutationFn: async (formData: FormData) => {
            const { data } = await axios.post<{ uuid: string }>(
                "/document-template-alliances/data/admin",
                formData,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
            return data;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAllianceKeys.all });
        },
    });
}

export function useUpdateDocumentTemplateAlliance(): ReturnType<typeof useMutation<void, Error, { uuid: string; formData: FormData }>> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; formData: FormData }>({
        mutationFn: async ({ uuid, formData }) => {
            await axios.post(
                `/document-template-alliances/data/admin/${uuid}`,
                formData,
                { headers: { "Content-Type": "multipart/form-data" } },
            );
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAllianceKeys.all });
        },
    });
}
