import { useMutation, useQueryClient } from '@tanstack/react-query';
import axios from 'axios';
import type { AxiosError } from 'axios';
import { sileo } from 'sileo';
import type {
  CreatePermissionPayload,
  SyncRolePermissionsPayload,
  SyncUserAccessPayload,
} from '@/modules/access-control/types';

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

export function useAccessControlMutations() {
  const queryClient = useQueryClient();

  const createPermission = useMutation({
    mutationFn: (payload: CreatePermissionPayload) => axios.post('/permissions/data/admin/permissions', payload),
    onSuccess: () => {
      sileo.success({ title: 'Permission created successfully' });
      queryClient.invalidateQueries({ queryKey: ['access-control', 'permissions'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to create permission') });
    },
  });

  const syncRolePermissions = useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: SyncRolePermissionsPayload }) =>
      axios.put(`/permissions/data/admin/roles/${uuid}/permissions`, payload),
    onSuccess: () => {
      sileo.success({ title: 'Role permissions updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['access-control', 'roles'] });
      queryClient.invalidateQueries({ queryKey: ['access-control', 'permissions'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update role permissions') });
    },
  });

  const syncUserAccess = useMutation({
    mutationFn: ({ uuid, payload }: { uuid: string; payload: SyncUserAccessPayload }) =>
      axios.put(`/permissions/data/admin/users/${uuid}/access`, payload),
    onSuccess: (_, variables) => {
      sileo.success({ title: 'User access updated successfully' });
      queryClient.invalidateQueries({ queryKey: ['access-control', 'user-access', variables.uuid] });
      queryClient.invalidateQueries({ queryKey: ['access-control', 'roles'] });
      queryClient.invalidateQueries({ queryKey: ['users'] });
    },
    onError: (error: AxiosError | Error | unknown) => {
      sileo.error({ title: getErrorMessage(error, 'Failed to update user access') });
    },
  });

  return {
    createPermission,
    syncRolePermissions,
    syncUserAccess,
  };
}
