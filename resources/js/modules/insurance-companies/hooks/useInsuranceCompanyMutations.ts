import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type {
    CreateInsuranceCompanyPayload,
    UpdateInsuranceCompanyPayload,
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

export const useInsuranceCompanyMutations = () => {
    const queryClient = useQueryClient();

    const createInsuranceCompany = useMutation({
        mutationFn: (payload: CreateInsuranceCompanyPayload) => axios.post('/insurance-companies/data/admin', payload),
        onSuccess: () => {
            sileo.success({ title: 'Insurance company created successfully' });
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create insurance company') });
        },
    });

    const updateInsuranceCompany = useMutation({
        mutationFn: ({ uuid, payload }: { uuid: string; payload: UpdateInsuranceCompanyPayload }) =>
            axios.put(`/insurance-companies/data/admin/${uuid}`, payload),
        onSuccess: (_, variables) => {
            sileo.success({ title: 'Insurance company updated successfully' });
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
            queryClient.invalidateQueries({ queryKey: ['insurance-company', variables.uuid] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update insurance company') });
        },
    });

    const deleteInsuranceCompany = useMutation({
        mutationFn: (uuid: string) => axios.delete(`/insurance-companies/data/admin/${uuid}`),
        onSuccess: () => {
            sileo.success({ title: 'Insurance company deleted successfully' });
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete insurance company') });
        },
    });

    const restoreInsuranceCompany = useMutation({
        mutationFn: (uuid: string) => axios.patch(`/insurance-companies/data/admin/${uuid}/restore`),
        onSuccess: (_, uuid) => {
            sileo.success({ title: 'Insurance company restored successfully' });
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
            queryClient.invalidateQueries({ queryKey: ['insurance-company', uuid] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore insurance company') });
        },
    });

    const bulkDeleteInsuranceCompanies = useMutation({
        mutationFn: (uuids: string[]) => axios.post('/insurance-companies/data/admin/bulk-delete', { uuids }),
        onSuccess: () => {
            sileo.success({ title: 'Selected insurance companies deleted successfully' });
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
        },
        onError: (error: AxiosError | Error | unknown) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete selected insurance companies') });
        },
    });

    return {
        createInsuranceCompany,
        updateInsuranceCompany,
        deleteInsuranceCompany,
        restoreInsuranceCompany,
        bulkDeleteInsuranceCompanies,
    };
};
