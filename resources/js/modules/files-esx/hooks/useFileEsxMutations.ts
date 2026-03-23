import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type { UpdateFileEsxPayload } from '../types';

function getErrorMessage(error: AxiosError | Error | unknown, fallback: string): string {
    if (axios.isAxiosError(error)) {
        const responseData = error.response?.data as { message?: string } | undefined;

        if (typeof responseData?.message === 'string' && responseData.message.length > 0) {
            return responseData.message;
        }
    }

    if (error instanceof Error && error.message.length > 0) {
        return error.message;
    }

    return fallback;
}

export interface CreateFileEsxFormPayload {
    file: File;
    file_name?: string;
}

export const useFileEsxMutations = () => {
    const queryClient = useQueryClient();

    const invalidate = () => queryClient.invalidateQueries({ queryKey: ['files-esx'] });

    const createFileEsx = useMutation({
        mutationFn: (payload: CreateFileEsxFormPayload) => {
            const formData = new FormData();
            formData.append('file', payload.file);

            if (payload.file_name) {
                formData.append('file_name', payload.file_name);
            }

            return axios.post<{ uuid: string; message: string }>(
                '/files-esx/data/admin',
                formData,
                { headers: { 'Content-Type': 'multipart/form-data' } },
            );
        },
        onSuccess: () => {
            sileo.success({ title: 'File ESX created successfully.' });
            invalidate();
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create File ESX.') });
        },
    });

    const updateFileEsx = useMutation({
        mutationFn: ({ uuid, payload }: { uuid: string; payload: UpdateFileEsxPayload }) =>
            axios.put<{ message: string }>(`/files-esx/data/admin/${uuid}`, payload),
        onSuccess: (_, variables) => {
            sileo.success({ title: 'File ESX updated successfully.' });
            invalidate();
            queryClient.invalidateQueries({ queryKey: ['files-esx', variables.uuid] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update File ESX.') });
        },
    });

    const deleteFileEsx = useMutation({
        mutationFn: (uuid: string) =>
            axios.delete<{ message: string }>(`/files-esx/data/admin/${uuid}`),
        onSuccess: () => {
            sileo.success({ title: 'File ESX permanently deleted.' });
            invalidate();
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete File ESX.') });
        },
    });

    const bulkDeleteFilesEsx = useMutation({
        mutationFn: (uuids: string[]) =>
            axios.post<{ message: string; deleted_count: number }>(
                '/files-esx/data/admin/bulk-delete',
                { uuids },
            ),
        onSuccess: (response) => {
            const count = response.data.deleted_count;
            sileo.success({ title: `${count} file(s) permanently deleted.` });
            invalidate();
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete files ESX.') });
        },
    });

    return {
        createFileEsx,
        updateFileEsx,
        deleteFileEsx,
        bulkDeleteFilesEsx,
    };
};
