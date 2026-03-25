import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { CustomerFormData } from '../types';

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

export function useCreateCustomer() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, CustomerFormData>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/customers/data/admin',
                payload,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['customers'] });
            sileo.success({ title: 'Customer created successfully.' });
            router.visit('/customers');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create customer.') });
        },
    });
}

export function useUpdateCustomer() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, { uuid: string; data: CustomerFormData }>({
        mutationFn: async ({ uuid, data: payload }) => {
            const { data } = await axios.put<MutationMessageResponse>(
                `/customers/data/admin/${uuid}`,
                payload,
            );

            return data;
        },
        onSuccess: async (_response, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['customers'] });
            await queryClient.invalidateQueries({
                queryKey: ['customers', 'detail', variables.uuid],
            });
            sileo.success({ title: 'Customer updated successfully.' });
            router.visit('/customers');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update customer.') });
        },
    });
}

export function useDeleteCustomer() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationMessageResponse>(
                `/customers/data/admin/${uuid}`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['customers'] });
            sileo.success({ title: 'Customer deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete customer.') });
        },
    });
}

export function useRestoreCustomer() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationMessageResponse>(
                `/customers/data/admin/${uuid}/restore`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['customers'] });
            sileo.success({ title: 'Customer restored successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore customer.') });
        },
    });
}

export function useBulkDeleteCustomers() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/customers/data/admin/bulk-delete',
                { uuids },
            );

            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['customers'] });
            sileo.success({ title: response.message });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete customers.') });
        },
    });
}
