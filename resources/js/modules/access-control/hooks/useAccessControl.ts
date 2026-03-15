import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import type {
  AccessControlResponse,
  AccessRoleItem,
  PermissionCatalogItem,
  UserAccessDetail,
  UserSearchResult,
} from '@/modules/access-control/types';

export function usePermissionCatalog(search: string, enabled: boolean = true) {
  return useQuery<AccessControlResponse<PermissionCatalogItem[]>, Error>({
    queryKey: ['access-control', 'permissions', search],
    queryFn: async () => {
      const { data } = await axios.get<AccessControlResponse<PermissionCatalogItem[]>>('/permissions/data/admin/permissions', {
        params: {
          search: search || undefined,
        },
      });

      return data;
    },
    enabled,
  });
}

export function useAccessRoles(enabled: boolean = true) {
  return useQuery<AccessControlResponse<AccessRoleItem[]>, Error>({
    queryKey: ['access-control', 'roles'],
    queryFn: async () => {
      const { data } = await axios.get<AccessControlResponse<AccessRoleItem[]>>('/permissions/data/admin/roles');

      return data;
    },
    enabled,
  });
}

export function useUserSearch(search: string, enabled: boolean = true) {
  return useQuery<AccessControlResponse<UserSearchResult[]>, Error>({
    queryKey: ['access-control', 'users', search],
    queryFn: async () => {
      const { data } = await axios.get<AccessControlResponse<UserSearchResult[]>>('/permissions/data/admin/users', {
        params: {
          search: search || undefined,
          limit: 10,
        },
      });

      return data;
    },
    enabled: enabled && search.trim().length > 0,
  });
}

export function useUserAccess(userUuid: string | null, enabled: boolean = true) {
  return useQuery<AccessControlResponse<UserAccessDetail>, Error>({
    queryKey: ['access-control', 'user-access', userUuid],
    queryFn: async () => {
      const { data } = await axios.get<AccessControlResponse<UserAccessDetail>>(`/permissions/data/admin/users/${userUuid}`);

      return data;
    },
    enabled: enabled && typeof userUuid === 'string' && userUuid.length > 0,
  });
}
