import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import { sileo } from 'sileo';
import { documentTemplateAdjusterKeys } from './useDocumentTemplateAdjusters';

export function useCreateDocumentTemplateAdjuster(): ReturnType<
    typeof useMutation<{ uuid: string }, Error, FormData>
> {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string }, Error, FormData>({
        mutationFn: async (formData: FormData) => {
            const { data } = await axios.post<{ uuid: string }>(
                '/document-template-adjusters/data/admin',
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
            return data;
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAdjusterKeys.all });
            sileo.success({ title: 'Template created successfully.' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to create template. Please try again.' });
        },
    });
}

export function useUpdateDocumentTemplateAdjuster(): ReturnType<
    typeof useMutation<void, Error, { uuid: string; formData: FormData }>
> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; formData: FormData }>({
        mutationFn: async ({ uuid, formData }) => {
            await axios.post(
                `/document-template-adjusters/data/admin/${uuid}`,
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAdjusterKeys.all });
            sileo.success({ title: 'Template updated successfully.' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to update template. Please try again.' });
        },
    });
}

export function useDeleteDocumentTemplateAdjuster(): ReturnType<
    typeof useMutation<void, Error, string>
> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid: string) => {
            await axios.delete(`/document-template-adjusters/data/admin/${uuid}`);
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAdjusterKeys.all });
            sileo.success({ title: 'Template deleted successfully.' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to delete template. Please try again.' });
        },
    });
}

export function useBulkDeleteDocumentTemplateAdjusters(): ReturnType<
    typeof useMutation<void, Error, string[]>
> {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids: string[]) => {
            await axios.post('/document-template-adjusters/data/admin/bulk-delete', { uuids });
        },
        onSuccess: () => {
            void queryClient.invalidateQueries({ queryKey: documentTemplateAdjusterKeys.all });
            sileo.success({ title: 'Templates deleted successfully.' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to delete templates. Please try again.' });
        },
    });
}
