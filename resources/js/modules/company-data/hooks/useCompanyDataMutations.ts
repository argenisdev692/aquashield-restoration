import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type { CreateCompanyDataDTO, UpdateCompanyDataDTO } from '@/modules/company-data/types';

function getErrorMessage(error: AxiosError | Error | unknown, fallback: string): string {
  const axiosError = error as AxiosError<{ message?: string }>;
  return axiosError.response?.data?.message ?? axiosError.message ?? fallback;
}

/**
 * useCompanyDataMutations — Mutations for updating company data.
 */
export const useCompanyDataMutations = () => {
  const queryClient = useQueryClient();

  const createCompanyData = useMutation({
    mutationFn: (payload: CreateCompanyDataDTO) => {
      return axios.post('/company-data/data/admin', payload);
    },
    onSuccess: async () => {
      sileo.success({ title: 'Company created successfully' });
      await queryClient.invalidateQueries({ queryKey: ['company-data'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to create company') });
    },
  });

  const updateCompanyData = useMutation({
    mutationFn: ({ companyUuid, payload }: { companyUuid?: string; payload: UpdateCompanyDataDTO }) => {
      const url = companyUuid ? `/company-data/data/admin/${companyUuid}` : '/company-data/data/me';
      return axios.put(url, payload);
    },
    onSuccess: async (_, variables) => {
      sileo.success({ title: 'Company updated successfully' });
      await Promise.all([
        queryClient.invalidateQueries({ queryKey: ['company-data', 'detail', variables.companyUuid || 'me'] }),
        queryClient.invalidateQueries({ queryKey: ['company-data'] }),
      ]);
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update company') });
    },
  });

  const deleteCompanyData = useMutation({
    mutationFn: (uuid: string | string[]) => {
      const uuids = Array.isArray(uuid) ? uuid.join(',') : uuid;
      return axios.delete(`/company-data/data/admin/${uuids}`);
    },
    onSuccess: async () => {
      sileo.success({ title: 'Company deleted successfully' });
      await queryClient.invalidateQueries({ queryKey: ['company-data'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete company') });
    },
  });

  const restoreCompanyData = useMutation({
    mutationFn: (uuid: string | string[]) => {
      const uuids = Array.isArray(uuid) ? uuid.join(',') : uuid;
      return axios.patch(`/company-data/data/admin/${uuids}/restore`);
    },
    onSuccess: async () => {
      sileo.success({ title: 'Company restored successfully' });
      await queryClient.invalidateQueries({ queryKey: ['company-data'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to restore company') });
    },
  });

  return {
    createCompanyData,
    updateCompanyData,
    deleteCompanyData,
    restoreCompanyData,
  };
};
