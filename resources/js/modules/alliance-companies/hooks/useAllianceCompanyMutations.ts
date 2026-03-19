import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { AllianceCompanyFormData } from '../types';

interface MutationMessageResponse {
    message: string;
    uuid?: string;
    deleted_count?: number;
}

function getErrorMessage(error: unknown, fallbackMessage: string): string {
    if (isAxiosError<{ message?: string }>(error)) {
        return error.response?.data?.message ?? error.message ?? fallbackMessage;
    }

    if (error instanceof Error) {
        return error.message;
    }

    return fallbackMessage;
}

export function useCreateAllianceCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, AllianceCompanyFormData>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/alliance-companies/data/admin',
                payload,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: 'Alliance company created successfully.' });
            router.visit('/alliance-companies');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create alliance company.') });
        },
    });
}

export function useUpdateAllianceCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, { uuid: string; data: AllianceCompanyFormData }>({
        mutationFn: async ({ uuid, data: payload }) => {
            const { data } = await axios.put<MutationMessageResponse>(
                `/alliance-companies/data/admin/${uuid}`,
                payload,
            );

            return data;
        },
        onSuccess: async (_response, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            await queryClient.invalidateQueries({
                queryKey: ['alliance-companies', 'detail', variables.uuid],
            });
            sileo.success({ title: 'Alliance company updated successfully.' });
            router.visit('/alliance-companies');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update alliance company.') });
        },
    });
}

export function useDeleteAllianceCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationMessageResponse>(
                `/alliance-companies/data/admin/${uuid}`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: 'Alliance company deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete alliance company.') });
        },
    });
}

export function useRestoreAllianceCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationMessageResponse>(
                `/alliance-companies/data/admin/${uuid}/restore`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: 'Alliance company restored successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore alliance company.') });
        },
    });
}

export function useBulkDeleteAllianceCompanies() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/alliance-companies/data/admin/bulk-delete',
                { uuids },
            );

            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: response.message });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete alliance companies.') });
        },
    });
}
