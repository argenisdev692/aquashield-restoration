/**
 * Users — TypeScript types for the Users CRUD module.
 *
 * Used across modules/users/ hooks and pages/users/ components.
 */

export interface UserListItem {
  id: number;
  uuid: string;
  name: string;
  last_name: string | null;
  full_name: string;
  email: string | null;
  username: string | null;
  phone: string | null;
  profile_photo_path: string | null;
  status: 'active' | 'suspended' | 'banned' | 'deleted';
  created_at: string | null;
  updated_at: string | null;
  deleted_at?: string | null;
}

export interface UserDetail extends UserListItem {
  address: string | null;
  city: string | null;
  state: string | null;
  country: string | null;
  zip_code: string | null;
}

export interface CreateUserPayload {
  name: string;
  email: string;
  last_name?: string;
  username?: string;
  phone?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  zip_code?: string;
  password?: string;
}

export interface UpdateUserPayload {
  name?: string;
  email?: string;
  last_name?: string;
  username?: string;
  phone?: string;
  address?: string;
  city?: string;
  state?: string;
  country?: string;
  zip_code?: string;
}

export interface UserFilters {
  page?: number;
  perPage?: number;
  search?: string;
  status?: string;
  dateFrom?: string;
  dateTo?: string;
  sortBy?: string;
  sortDir?: 'asc' | 'desc';
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
