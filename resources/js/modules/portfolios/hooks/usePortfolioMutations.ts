import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import type { PortfolioFormData, PortfolioImage } from '../types';

export function useCreatePortfolio() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, PortfolioFormData>({
        mutationFn: async (data) => {
            const response = await fetch('/portfolios/data/admin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to create portfolio.');
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios'] });
            router.visit('/portfolios');
        },
    });
}

export function useUpdatePortfolio() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: PortfolioFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/portfolios/data/admin/${uuid}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to update portfolio.');
            }
        },
        onSuccess: async (_data, variables) => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios'] });
            router.visit(`/portfolios/${variables.uuid}`);
        },
    });
}

export function useDeletePortfolio() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/portfolios/data/admin/${uuid}`, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error('Failed to delete portfolio.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios'] });
        },
    });
}

export function useRestorePortfolio() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/portfolios/data/admin/${uuid}/restore`, {
                method: 'PATCH',
            });

            if (!response.ok) {
                throw new Error('Failed to restore portfolio.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios'] });
        },
    });
}

export function useBulkDeletePortfolios() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch('/portfolios/data/admin/bulk-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error('Failed to bulk delete portfolios.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios'] });
        },
    });
}

export function useUploadPortfolioImage(portfolioUuid: string) {
    const queryClient = useQueryClient();

    return useMutation<PortfolioImage, Error, File>({
        mutationFn: async (file) => {
            const formData = new FormData();
            formData.append('image', file);

            const response = await fetch(`/portfolios/data/admin/${portfolioUuid}/images`, {
                method: 'POST',
                body: formData,
            });

            if (!response.ok) {
                throw new Error('Failed to upload image.');
            }

            return response.json() as Promise<PortfolioImage>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios', 'detail', portfolioUuid] });
        },
    });
}

export function useDeletePortfolioImage(portfolioUuid: string) {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (imageUuid) => {
            const response = await fetch(`/portfolios/data/admin/${portfolioUuid}/images/${imageUuid}`, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error('Failed to delete image.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['portfolios', 'detail', portfolioUuid] });
            await queryClient.invalidateQueries({ queryKey: ['portfolios', 'list'] });
        },
    });
}
