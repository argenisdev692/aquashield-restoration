import { router } from '@inertiajs/react';
import { useMutation, useQueryClient } from '@tanstack/react-query';
import type { ProjectTypeFormData } from '../types';

export function useCreateProjectType() {
    const queryClient = useQueryClient();

    return useMutation<{ uuid: string; message: string }, Error, ProjectTypeFormData>({
        mutationFn: async (data) => {
            const response = await fetch('/project-types/data/admin', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to create project type.');
            }

            return response.json() as Promise<{ uuid: string; message: string }>;
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['project-types'] });
            router.visit('/project-types');
        },
    });
}

export function useUpdateProjectType() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, { uuid: string; data: ProjectTypeFormData }>({
        mutationFn: async ({ uuid, data }) => {
            const response = await fetch(`/project-types/data/admin/${uuid}`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });

            if (!response.ok) {
                throw new Error('Failed to update project type.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['project-types'] });
            router.visit('/project-types');
        },
    });
}

export function useDeleteProjectType() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/project-types/data/admin/${uuid}`, {
                method: 'DELETE',
            });

            if (!response.ok) {
                throw new Error('Failed to delete project type.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['project-types'] });
        },
    });
}

export function useRestoreProjectType() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string>({
        mutationFn: async (uuid) => {
            const response = await fetch(`/project-types/data/admin/${uuid}/restore`, {
                method: 'PATCH',
            });

            if (!response.ok) {
                throw new Error('Failed to restore project type.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['project-types'] });
        },
    });
}

export function useBulkDeleteProjectTypes() {
    const queryClient = useQueryClient();

    return useMutation<void, Error, string[]>({
        mutationFn: async (uuids) => {
            const response = await fetch('/project-types/data/admin/bulk-delete', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ uuids }),
            });

            if (!response.ok) {
                throw new Error('Failed to bulk delete project types.');
            }
        },
        onSuccess: async () => {
            await queryClient.invalidateQueries({ queryKey: ['project-types'] });
        },
    });
}
