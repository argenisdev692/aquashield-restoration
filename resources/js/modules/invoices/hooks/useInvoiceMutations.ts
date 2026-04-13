import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { StoreInvoicePayload } from '../types';

interface MutationResponse {
    message: string;
    uuid?: string;
}

function getErrorMessage(error: unknown, fallback: string): string {
    if (isAxiosError<{ message?: string }>(error)) {
        return error.response?.data?.message ?? error.message ?? fallback;
    }
    if (error instanceof Error) return error.message;
    return fallback;
}

export function useCreateInvoice() {
    const queryClient = useQueryClient();

    return useMutation<MutationResponse, Error, StoreInvoicePayload>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<MutationResponse>(
                '/invoices/data/admin',
                payload,
            );
            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['invoices'] });
            sileo.success({ title: 'Invoice created successfully.' });
            if (response.uuid) {
                router.visit(`/invoices/${response.uuid}`);
            } else {
                router.visit('/invoices');
            }
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create invoice.') });
        },
    });
}

export function useUpdateInvoice() {
    const queryClient = useQueryClient();

    return useMutation<MutationResponse, Error, { uuid: string; data: StoreInvoicePayload }>({
        mutationFn: async ({ uuid, data: payload }) => {
            const { data } = await axios.put<MutationResponse>(
                `/invoices/data/admin/${uuid}`,
                payload,
            );
            return data;
        },
        onSuccess: async (_res, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['invoices'] });
            await queryClient.invalidateQueries({ queryKey: ['invoices', 'detail', variables.uuid] });
            sileo.success({ title: 'Invoice updated successfully.' });
            router.visit(`/invoices/${variables.uuid}`);
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update invoice.') });
        },
    });
}

export function useDeleteInvoice() {
    const queryClient = useQueryClient();

    return useMutation<MutationResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationResponse>(
                `/invoices/data/admin/${uuid}`,
            );
            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['invoices'] });
            sileo.success({ title: 'Invoice deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete invoice.') });
        },
    });
}

export function useRestoreInvoice() {
    const queryClient = useQueryClient();

    return useMutation<MutationResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationResponse>(
                `/invoices/data/admin/${uuid}/restore`,
            );
            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['invoices'] });
            sileo.success({ title: 'Invoice restored successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore invoice.') });
        },
    });
}

export function useBulkDeleteInvoices() {
    const queryClient = useQueryClient();

    return useMutation<MutationResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationResponse>(
                '/invoices/data/admin/bulk-delete',
                { uuids },
            );
            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['invoices'] });
            sileo.success({ title: response.message ?? 'Invoices deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete invoices.') });
        },
    });
}
