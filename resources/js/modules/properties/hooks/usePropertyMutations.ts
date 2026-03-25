import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { PropertyFormData } from '../types';

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

export function useCreateProperty() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, PropertyFormData>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/properties/data/admin',
                payload,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['properties'] });
            sileo.success({ title: 'Property created successfully.' });
            router.visit('/properties');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create property.') });
        },
    });
}

export function useUpdateProperty() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, { uuid: string; data: PropertyFormData }>({
        mutationFn: async ({ uuid, data: payload }) => {
            const { data } = await axios.put<MutationMessageResponse>(
                `/properties/data/admin/${uuid}`,
                payload,
            );

            return data;
        },
        onSuccess: async (_response, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['properties'] });
            await queryClient.invalidateQueries({
                queryKey: ['properties', 'detail', variables.uuid],
            });
            sileo.success({ title: 'Property updated successfully.' });
            router.visit('/properties');
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update property.') });
        },
    });
}

export function useDeleteProperty() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationMessageResponse>(
                `/properties/data/admin/${uuid}`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['properties'] });
            sileo.success({ title: 'Property deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete property.') });
        },
    });
}

export function useRestoreProperty() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationMessageResponse>(
                `/properties/data/admin/${uuid}/restore`,
            );

            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['properties'] });
            sileo.success({ title: 'Property restored successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore property.') });
        },
    });
}

export function useBulkDeleteProperties() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/properties/data/admin/bulk-delete',
                { uuids },
            );

            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['properties'] });
            sileo.success({ title: response.message });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete properties.') });
        },
    });
}
