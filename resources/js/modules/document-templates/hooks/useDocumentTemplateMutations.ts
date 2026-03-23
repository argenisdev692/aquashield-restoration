import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import { documentTemplateKeys } from './useDocumentTemplates';

export function useCreateDocumentTemplate(): ReturnType<
    typeof useMutation<{ uuid: string }, Error, FormData>
> {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string }, Error, FormData>({
        mutationFn: async (formData: FormData) => {
            const { data } = await axios.post<{ uuid: string }>(
                '/document-templates/data/admin',
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
            return data;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateKeys.all });
        },
    });
}

export function useUpdateDocumentTemplate(): ReturnType<
    typeof useMutation<void, Error, { uuid: string; formData: FormData }>
> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; formData: FormData }>({
        mutationFn: async ({ uuid, formData }) => {
            await axios.post(
                `/document-templates/data/admin/${uuid}`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateKeys.all });
        },
    });
}

export function useDeleteDocumentTemplate(): ReturnType<
    typeof useMutation<void, Error, string>
> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid: string) => {
            await axios.delete(`/document-templates/data/admin/${uuid}`);
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateKeys.all });
        },
    });
}

export function useBulkDeleteDocumentTemplates(): ReturnType<
    typeof useMutation<void, Error, string[]>
> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids: string[]) => {
            await axios.post('/document-templates/data/admin/bulk-delete', { uuids });
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateKeys.all });
        },
    });
}
