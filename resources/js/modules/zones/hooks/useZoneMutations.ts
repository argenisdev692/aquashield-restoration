import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import type { ZoneFormData } from '../types';

function getCsrfToken(): string {
    const meta = document.querySelector<HTMLMetaElement>('meta[name="csrf-token"]');
    return meta?.content ?? '';
}

function getErrorMessage(err: unknown, defaultMsg: string): string {
    if (
        err !== null &&
        typeof err === 'object' &&
        'message' in err &&
        typeof (err as { message: unknown }).message === 'string'
    ) {
        return (err as { message: string }).message;
    }
    return defaultMsg;
}

export function useCreateZone() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, ZoneFormData>({
        mutationFn: async (data) => {
            const response = await fetch('/zones/data/admin', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const body = (await response.json().catch(() => ({}))) as { message?: string };
                throw new Error(body.message ?? 'Failed to create zone.');
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['zones'] });
            router.visit('/zones');
        },
    });
}

export function useUpdateZone() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: ZoneFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/zones/data/admin/${uuid}`, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                const body = (await response.json().catch(() => ({}))) as { message?: string };
                throw new Error(body.message ?? 'Failed to update zone.');
            }
        },
        onSuccess: async (_data, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['zones'] });
            router.visit(`/zones/${variables.uuid}`);
        },
    });
}

export function useDeleteZone() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/zones/data/admin/${uuid}`, {
                method: 'DELETE',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            });

            if (!response.ok) {
                const body = (await response.json().catch(() => ({}))) as { message?: string };
                throw new Error(body.message ?? 'Failed to delete zone.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['zones'] });
        },
        onError: (err) => {
            console.error(getErrorMessage(err, 'Delete zone failed'));
        },
    });
}

export function useRestoreZone() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/zones/data/admin/${uuid}/restore`, {
                method: 'PATCH',
                headers: { 'X-CSRF-TOKEN': getCsrfToken() },
            });

            if (!response.ok) {
                const body = (await response.json().catch(() => ({}))) as { message?: string };
                throw new Error(body.message ?? 'Failed to restore zone.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['zones'] });
        },
        onError: (err) => {
            console.error(getErrorMessage(err, 'Restore zone failed'));
        },
    });
}

export function useBulkDeleteZones() {
    const queryClient = useQueryClient();

    return useMutation<{ deleted_count: number }, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch('/zones/data/admin/bulk-delete', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': getCsrfToken(),
                },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                const body = (await response.json().catch(() => ({}))) as { message?: string };
                throw new Error(body.message ?? 'Failed to bulk delete zones.');
            }

            return response.json() as Promise<{ deleted_count: number }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['zones'] });
        },
        onError: (err) => {
            console.error(getErrorMessage(err, 'Bulk delete zones failed'));
        },
    });
}
