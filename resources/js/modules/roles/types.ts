export interface RoleListItem {
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

export interface RoleDetail extends RoleListItem {}

export interface CreateRolePayload {
  name: string;
}

export interface UpdateRolePayload {
  name: string;
}

export interface RoleFilters {
  page?: number;
  per_page?: number;
  search?: string;
  sort_by?: 'name' | 'created_at' | 'updated_at';
  sort_dir?: 'asc' | 'desc';
}

export interface PaginatedResponse<T> {
  data: T[];
  meta: {
    currentPage: number;
    lastPage: number;
    perPage: number;
    total: number;
  };
}
