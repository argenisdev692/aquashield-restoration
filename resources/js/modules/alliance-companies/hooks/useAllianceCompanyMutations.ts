import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { sileo } from 'sileo';
import { AllianceCompany } from '../types';

export const useAllianceCompanyMutations = () => {
    const queryClient = useQueryClient();

    const createMutation = useMutation({
        mutationFn: async (data: Partial<AllianceCompany>) => {
            const response = await axios.post('/alliance-companies/data', data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: 'Alliance company created successfully' });
            router.visit('/alliance-companies');
        },
        onError: () => {
            sileo.error({ title: 'Failed to create Alliance company' });
        },
    });

    const updateMutation = useMutation({
        mutationFn: async ({ uuid, data }: { uuid: string; data: Partial<AllianceCompany> }) => {
            const response = await axios.put(`/alliance-companies/data/${uuid}`, data);
            return response.data.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            queryClient.invalidateQueries({ queryKey: ['alliance-company', data.uuid] });
            sileo.success({ title: 'Alliance company updated successfully' });
            router.visit('/alliance-companies');
        },
        onError: () => {
            sileo.error({ title: 'Failed to update Alliance company' });
        },
    });

    const deleteMutation = useMutation({
        mutationFn: async (uuid: string) => {
            await axios.delete(`/alliance-companies/data/${uuid}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: 'Alliance company deleted successfully' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to delete Alliance company' });
        },
    });

    const restoreMutation = useMutation({
        mutationFn: async (uuid: string) => {
            await axios.patch(`/alliance-companies/data/${uuid}/restore`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['alliance-companies'] });
            sileo.success({ title: 'Alliance company restored successfully' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to restore Alliance company' });
        },
    });

    return {
        createAllianceCompany: createMutation,
        updateAllianceCompany: updateMutation,
        deleteAllianceCompany: deleteMutation,
        restoreAllianceCompany: restoreMutation,
    };
};
