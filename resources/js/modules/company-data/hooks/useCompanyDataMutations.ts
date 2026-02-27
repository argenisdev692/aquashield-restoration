import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type {
  CreateCompanyDataDTO,
  UpdateCompanyDataDTO,
} from '@/types/api';

async function createCompanyData(payload: CreateCompanyDataDTO): Promise<{ message: string; uuid: string }> {
  const { data } = await axios.post<{ message: string; uuid: string }>(
    '/api/company-data',
    payload
  );
  return data;
}

export function useCreateCompanyData() {
  const queryClient = useQueryClient();

  return useMutation<{ message: string; uuid: string }, Error, CreateCompanyDataDTO>({
    mutationFn: createCompanyData,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['company-data'] });
    },
  });
}

async function updateCompanyData({ uuid, payload }: { uuid: string; payload: UpdateCompanyDataDTO }): Promise<{ message: string }> {
  const { data } = await axios.put<{ message: string }>(
    `/api/company-data/${uuid}`,
    payload
  );
  return data;
}

export function useUpdateCompanyData() {
  const queryClient = useQueryClient();

  return useMutation<{ message: string }, Error, { uuid: string; payload: UpdateCompanyDataDTO }>({
    mutationFn: updateCompanyData,
    onSuccess: (_, { uuid }) => {
      queryClient.invalidateQueries({ queryKey: ['company-data'] });
      queryClient.invalidateQueries({ queryKey: ['company-data', 'detail', uuid] });
    },
  });
}

async function deleteCompanyData(uuids: string | string[]): Promise<void> {
  const uuidArray = Array.isArray(uuids) ? uuids : [uuids];
  // Normally bulk delete would be a different endpoint, but for now we loop.
  // In a real app with bulk delete, this should be a single request.
  await Promise.all(uuidArray.map(uuid => axios.delete(`/api/company-data/${uuid}`)));
}

export function useDeleteCompanyData() {
  const queryClient = useQueryClient();

  return useMutation<void, Error, string | string[]>({
    mutationFn: deleteCompanyData,
    onSuccess: () => {
      queryClient.invalidateQueries({ queryKey: ['company-data'] });
    },
  });
}

async function restoreCompanyData(uuids: string | string[]): Promise<void> {
    const uuidArray = Array.isArray(uuids) ? uuids : [uuids];
    await Promise.all(uuidArray.map(uuid => axios.patch(`/api/company-data/${uuid}/restore`)));
}

export function useRestoreCompanyData() {
    const queryClient = useQueryClient();
  
    return useMutation<void, Error, string | string[]>({
      mutationFn: restoreCompanyData,
      onSuccess: () => {
        queryClient.invalidateQueries({ queryKey: ['company-data'] });
      },
    });
}
