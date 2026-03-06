import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type { CreateUserPayload, UpdateUserPayload } from '@/types/users';

/**
 * useUserMutations — Provides mutations for creating, updating, and status management.
 */
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

export const useUserMutations = () => {
  const queryClient = useQueryClient();

  const createUser = useMutation({
    mutationFn: (payload: CreateUserPayload) => axios.post('/users/data/admin', payload),
    onSuccess: () => {
      sileo.success({ title: 'User created successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to create user') });
    },
  });

  const updateUser = useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: UpdateUserPayload }) =>
      axios.put(`/users/data/admin/${uuid}`, payload),
    onSuccess: (_, variables) => {
      sileo.success({ title: 'User updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
      queryClient.invalidateQueries({ queryKey: ['users', variables.uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update user') });
    },
  });

  const deleteUser = useMutation({
    mutationFn: (uuid: string) => axios.delete(`/users/data/admin/${uuid}`),
    onSuccess: () => {
      sileo.success({ title: 'User deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete user') });
    },
  });

  const restoreUser = useMutation({
    mutationFn: (uuid: string) => axios.patch(`/users/data/admin/${uuid}/restore`),
    onSuccess: (_, uuid) => {
      sileo.success({ title: 'User restored successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
      queryClient.invalidateQueries({ queryKey: ['users', uuid] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to restore user') });
    },
  });

  const suspendUser = useMutation({
    mutationFn: (uuid: string) => axios.post(`/users/data/admin/${uuid}/suspend`),
    onSuccess: () => {
      sileo.success({ title: 'User suspended successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to suspend user') });
    },
  });

  const activateUser = useMutation({
    mutationFn: (uuid: string) => axios.post(`/users/data/admin/${uuid}/activate`),
    onSuccess: () => {
      sileo.success({ title: 'User activated successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to activate user') });
    },
  });

  const bulkDeleteUsers = useMutation({
    mutationFn: (uuids: string[]) => axios.post('/users/data/admin/bulk-delete', { uuids }),
    onSuccess: () => {
      sileo.success({ title: 'Selected users deleted successfully' });
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to delete selected users') });
    },
  });

  return {
    createUser,
    updateUser,
    deleteUser,
    restoreUser,
    suspendUser,
    activateUser,
    bulkDeleteUsers,
  };
};
