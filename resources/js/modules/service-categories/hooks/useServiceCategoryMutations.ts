import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import type { ServiceCategoryFormData } from '../types';

export function useCreateServiceCategory() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, ServiceCategoryFormData>({
        mutationFn: async (data) => {
            const response = await fetch('/service-categories/data/admin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to create service category.');
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['service-categories'] });
            router.visit('/service-categories');
        },
    });
}

export function useUpdateServiceCategory() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: ServiceCategoryFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/service-categories/data/admin/${uuid}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to update service category.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['service-categories'] });
            router.visit('/service-categories');
        },
    });
}

export function useDeleteServiceCategory() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/service-categories/data/admin/${uuid}`, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error('Failed to delete service category.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['service-categories'] });
        },
    });
}

export function useRestoreServiceCategory() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/service-categories/data/admin/${uuid}/restore`, {
                method: 'PATCH',
            });

            if (!response.ok) {
                throw new Error('Failed to restore service category.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['service-categories'] });
        },
    });
}

export function useBulkDeleteServiceCategories() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch('/service-categories/data/admin/bulk-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error('Failed to bulk delete service categories.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['service-categories'] });
        },
    });
}
