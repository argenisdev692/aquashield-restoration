import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import { CreateCompanyDataDTO, UpdateCompanyDataDTO } from '@/types/api';

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
    onSuccess: () => {
      sileo.success({ title: 'Company created successfully' });
      queryClient.invalidateQueries({ queryKey: ['companies'] });
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
    onSuccess: (_, variables) => {
      sileo.success({ title: 'Company updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['company-data', variables.companyUuid || 'me'] });
      queryClient.invalidateQueries({ queryKey: ['companies'] });
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
    onSuccess: () => {
      sileo.success({ title: 'Company deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['companies'] });
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
    onSuccess: () => {
      sileo.success({ title: 'Company restored successfully' });
      queryClient.invalidateQueries({ queryKey: ['companies'] });
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
