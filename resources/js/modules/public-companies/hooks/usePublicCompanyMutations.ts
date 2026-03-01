import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { sileo } from 'sileo';
import { PublicCompany } from '../types';

export const usePublicCompanyMutations = () => {
    const queryClient = useQueryClient();

    const createMutation = useMutation({
        mutationFn: async (data: Partial<PublicCompany>) => {
            const response = await axios.post('/public-companies/data', data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['public-companies'] });
            sileo.success({ title: 'Public company created successfully' });
            router.visit('/public-companies');
        },
        onError: () => {
            sileo.error({ title: 'Failed to create Public company' });
        },
    });

    const updateMutation = useMutation({
        mutationFn: async ({ uuid, data }: { uuid: string; data: Partial<PublicCompany> }) => {
            const response = await axios.put(`/public-companies/data/${uuid}`, data);
            return response.data.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['public-companies'] });
            queryClient.invalidateQueries({ queryKey: ['public-company', data.uuid] });
            sileo.success({ title: 'Public company updated successfully' });
            router.visit('/public-companies');
        },
        onError: () => {
            sileo.error({ title: 'Failed to update Public company' });
        },
    });

    const deleteMutation = useMutation({
        mutationFn: async (uuid: string) => {
            await axios.delete(`/public-companies/data/${uuid}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['public-companies'] });
            sileo.success({ title: 'Public company deleted successfully' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to delete Public company' });
        },
    });

    const restoreMutation = useMutation({
        mutationFn: async (uuid: string) => {
            await axios.patch(`/public-companies/data/${uuid}/restore`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['public-companies'] });
            sileo.success({ title: 'Public company restored successfully' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to restore Public company' });
        },
    });

    return {
        createPublicCompany: createMutation,
        updatePublicCompany: updateMutation,
        deletePublicCompany: deleteMutation,
        restorePublicCompany: restoreMutation,
    };
};
