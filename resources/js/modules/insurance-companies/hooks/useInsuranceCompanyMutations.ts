import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import { router } from '@inertiajs/react';
import { sileo } from 'sileo';
import { InsuranceCompany } from '../types';

export const useInsuranceCompanyMutations = () => {
    const queryClient = useQueryClient();

    const createMutation = useMutation({
        mutationFn: async (data: Partial<InsuranceCompany>) => {
            const response = await axios.post('/insurance-companies/data/admin', data);
            return response.data.data;
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
            sileo.success({ title: 'Insurance company created successfully' });
            router.visit('/insurance-companies');
        },
        onError: () => {
            sileo.error({ title: 'Failed to create insurance company' });
        },
    });

    const updateMutation = useMutation({
        mutationFn: async ({ uuid, data }: { uuid: string; data: Partial<InsuranceCompany> }) => {
            const response = await axios.put(`/insurance-companies/data/admin/${uuid}`, data);
            return response.data.data;
        },
        onSuccess: (data) => {
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
            queryClient.invalidateQueries({ queryKey: ['insurance-company', data.uuid] });
            sileo.success({ title: 'Insurance company updated successfully' });
            router.visit('/insurance-companies');
        },
        onError: () => {
            sileo.error({ title: 'Failed to update insurance company' });
        },
    });

    const deleteMutation = useMutation({
        mutationFn: async (uuid: string) => {
            await axios.delete(`/insurance-companies/data/admin/${uuid}`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
            sileo.success({ title: 'Insurance company deleted successfully' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to delete insurance company' });
        },
    });

    const restoreMutation = useMutation({
        mutationFn: async (uuid: string) => {
            await axios.patch(`/insurance-companies/data/admin/${uuid}/restore`);
        },
        onSuccess: () => {
            queryClient.invalidateQueries({ queryKey: ['insurance-companies'] });
            sileo.success({ title: 'Insurance company restored successfully' });
        },
        onError: () => {
            sileo.error({ title: 'Failed to restore insurance company' });
        },
    });

    return {
        createInsuranceCompany: createMutation,
        updateInsuranceCompany: updateMutation,
        deleteInsuranceCompany: deleteMutation,
        restoreInsuranceCompany: restoreMutation,
    };
};
