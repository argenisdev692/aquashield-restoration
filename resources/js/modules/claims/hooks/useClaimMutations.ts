import axios, { isAxiosError } from 'axios';
import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import { sileo } from 'sileo';
import type { ClaimStorePayload } from '../types';

interface MutationMessageResponse {
    message: string;
    uuid?: string;
    deleted_count?: number;
}

function getErrorMessage(error: unknown, fallback: string): string {
    if (isAxiosError<{ message?: string }>(error)) {
        return error.response?.data?.message ?? error.message ?? fallback;
    }
    if (error instanceof Error) return error.message;
    return fallback;
}

export function useCreateClaim() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, ClaimStorePayload>({
        mutationFn: async (payload) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/claims/data/admin',
                payload,
            );
            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['claims'] });
            sileo.success({ title: 'Claim created successfully.' });
            if (response.uuid) {
                router.visit(`/claims/${response.uuid}`);
            } else {
                router.visit('/claims');
            }
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to create claim.') });
        },
    });
}

export function useUpdateClaim() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, { uuid: string; data: ClaimStorePayload }>({
        mutationFn: async ({ uuid, data: payload }) => {
            const { data } = await axios.put<MutationMessageResponse>(
                `/claims/data/admin/${uuid}`,
                payload,
            );
            return data;
        },
        onSuccess: async (_res, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['claims'] });
            await queryClient.invalidateQueries({ queryKey: ['claims', 'detail', variables.uuid] });
            sileo.success({ title: 'Claim updated successfully.' });
            router.visit(`/claims/${variables.uuid}`);
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to update claim.') });
        },
    });
}

export function useDeleteClaim() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.delete<MutationMessageResponse>(
                `/claims/data/admin/${uuid}`,
            );
            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['claims'] });
            sileo.success({ title: 'Claim deleted successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to delete claim.') });
        },
    });
}

export function useRestoreClaim() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string>({
        mutationFn: async (uuid) => {
            const { data } = await axios.patch<MutationMessageResponse>(
                `/claims/data/admin/${uuid}/restore`,
            );
            return data;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['claims'] });
            sileo.success({ title: 'Claim restored successfully.' });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to restore claim.') });
        },
    });
}

export function useBulkDeleteClaims() {
    const queryClient = useQueryClient();

    return useMutation<MutationMessageResponse, Error, string[]>({
        mutationFn: async (uuids) => {
            const { data } = await axios.post<MutationMessageResponse>(
                '/claims/data/admin/bulk-delete',
                { uuids },
            );
            return data;
        },
        onSuccess: async (response) => {
            await queryClient.invalidateQueries({ queryKey: ['claims'] });
            sileo.success({ title: response.message });
        },
        onError: (error) => {
            sileo.error({ title: getErrorMessage(error, 'Failed to bulk delete claims.') });
        },
    });
}
