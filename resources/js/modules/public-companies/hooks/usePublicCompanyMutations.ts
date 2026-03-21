import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type {
    CreatePublicCompanyPayload,
    UpdatePublicCompanyPayload,
} from '../types';

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

export const usePublicCompanyMutations = () => {
    const queryClient = useQueryClient();

    const createPublicCompany = useMutation({
        mutationFn: (payload: CreatePublicCompanyPayload) => axios.post('/public-companies/data/admin', payload),
        onSuccess: async () => {
            sileo.success({ title: 'Public company created successfully' });
            await queryClient.invalidateQueries({ queryKey: ['public-companies'] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create public company') });
        },
    });

    const updatePublicCompany = useMutation({
        mutationFn: ({ uuid, payload }: { uuid: string; payload: UpdatePublicCompanyPayload }) =>
            axios.put(`/public-companies/data/admin/${uuid}`, payload),
        onSuccess: async (_, variables) => {
            sileo.success({ title: 'Public company updated successfully' });
            await Promise.all([
                queryClient.invalidateQueries({ queryKey: ['public-companies'] }),
                queryClient.invalidateQueries({ queryKey: ['public-company', variables.uuid] }),
            ]);
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update public company') });
        },
    });

    const deletePublicCompany = useMutation({
        mutationFn: (uuid: string) => axios.delete(`/public-companies/data/admin/${uuid}`),
        onSuccess: async () => {
            sileo.success({ title: 'Public company deleted successfully' });
            await queryClient.invalidateQueries({ queryKey: ['public-companies'] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete public company') });
        },
    });

    const restorePublicCompany = useMutation({
        mutationFn: (uuid: string) => axios.patch(`/public-companies/data/admin/${uuid}/restore`),
        onSuccess: async (_, uuid) => {
            sileo.success({ title: 'Public company restored successfully' });
            await Promise.all([
                queryClient.invalidateQueries({ queryKey: ['public-companies'] }),
                queryClient.invalidateQueries({ queryKey: ['public-company', uuid] }),
            ]);
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore public company') });
        },
    });

    return {
        createPublicCompany,
        updatePublicCompany,
        deletePublicCompany,
        restorePublicCompany,
    };
};
