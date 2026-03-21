import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { MortgageCompanyFormData } from '../types';

interface MutationMessageResponse {
    message: string;
    uuid?: string;
    deleted_count?: number;
}

function getErrorMessage(error: unknown, fallback: string): string {
    if (isAxiosError<{ message?: string }>(error)) {
        return error.response?.data?.message ?? error.message ?? fallback;
    }

    if (error instanceof Error) {
        return error.message;
    }

    return fallback;
}

export function useCreateMortgageCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, MortgageCompanyFormData>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/mortgage-companies/data/admin',
                payload,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
            sileo.success({ title: 'Mortgage company created successfully.' });
            router.visit('/mortgage-companies');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create mortgage company.') });
        },
    });
}

export function useUpdateMortgageCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, { uuid: string; data: MortgageCompanyFormData }>({
        mutationFn: async ({ uuid, data: payload }) => {
            const { data } = await axios.put<MutationMessageResponse>(
                `/mortgage-companies/data/admin/${uuid}`,
                payload,
            );

            return data;
        },
        onSuccess: async (_response, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
            await queryClient.invalidateQueries({
                queryKey: ['mortgage-companies', 'detail', variables.uuid],
            });
            sileo.success({ title: 'Mortgage company updated successfully.' });
            router.visit('/mortgage-companies');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update mortgage company.') });
        },
    });
}

export function useDeleteMortgageCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationMessageResponse>(
                `/mortgage-companies/data/admin/${uuid}`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
            sileo.success({ title: 'Mortgage company deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete mortgage company.') });
        },
    });
}

export function useRestoreMortgageCompany() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationMessageResponse>(
                `/mortgage-companies/data/admin/${uuid}/restore`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
            sileo.success({ title: 'Mortgage company restored successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore mortgage company.') });
        },
    });
}

export function useBulkDeleteMortgageCompanies() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/mortgage-companies/data/admin/bulk-delete',
                { uuids },
            );

            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['mortgage-companies'] });
            sileo.success({ title: response.message });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete mortgage companies.') });
        },
    });
}
