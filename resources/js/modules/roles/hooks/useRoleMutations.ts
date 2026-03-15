import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type { CreateRolePayload, UpdateRolePayload } from '@/modules/roles/types';

function getErrorMessage(error: AxiosError | Error | unknown, fallback: string): string {
  if (axios.isAxiosError(error)) {
    const responseData = error.response?.data as { message?: string } | undefined;

    if (typeof responseData?.message === 'string' && responseData.message.length > 0) {
      return responseData.message;
    }
  }

  if (error instanceof Error && error.message.length > 0) {
    return error.message;
  }

  return fallback;
}

export function useRoleMutations() {
  const queryClient = useQueryClient();

  const createRole = useMutation({
    mutationFn: (payload: CreateRolePayload) => axios.post('/roles/data/admin', payload),
    onSuccess: () => {
      sileo.success({ title: 'Role created successfully' });
      queryClient.invalidateQueries({ queryKey: ['roles'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to create role') });
    },
  });

  const updateRole = useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: UpdateRolePayload }) =>
      axios.put(`/roles/data/admin/${uuid}`, payload),
    onSuccess: (_, variables) => {
      sileo.success({ title: 'Role updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['roles'] });
      queryClient.invalidateQueries({ queryKey: ['roles', variables.uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update role') });
    },
  });

  const deleteRole = useMutation({
    mutationFn: (uuid: string) => axios.delete(`/roles/data/admin/${uuid}`),
    onSuccess: () => {
      sileo.success({ title: 'Role deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['roles'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete role') });
    },
  });

  const restoreRole = useMutation({
    mutationFn: (uuid: string) => axios.patch(`/roles/data/admin/${uuid}/restore`),
    onSuccess: (_, uuid) => {
      sileo.success({ title: 'Role restored successfully' });
      queryClient.invalidateQueries({ queryKey: ['roles'] });
      queryClient.invalidateQueries({ queryKey: ['roles', uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to restore role') });
    },
  });

  return {
    createRole,
    updateRole,
    deleteRole,
    restoreRole,
  };
}
