export interface PermissionCatalogItem {
  id: number;
  uuid: string;
  name: string;
  guard_name: string;
  roles_count: number;
  created_at: string | null;
  updated_at: string | null;
}

export interface AccessRoleItem {
  id: number;
  uuid: string;
  name: string;
  guard_name: string;
  permissions_count: number;
  permission_names: string[];
  created_at: string | null;
  updated_at: string | null;
  deleted_at: string | null;
}

export interface UserSearchResult {
  uuid: string;
  name: string;
  email: string | null;
}

export interface UserAccessDetail {
  uuid: string;
  name: string;
  email: string | null;
  roles: string[];
  direct_permissions: string[];
  effective_permissions: string[];
}

export interface AccessControlResponse<T> {
  data: T;
}

export interface CreatePermissionPayload {
  name: string;
}

export interface SyncRolePermissionsPayload {
  permissions: string[];
}

export interface SyncUserAccessPayload {
  roles: string[];
  permissions: string[];
}
