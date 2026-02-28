import { useQuery, keepPreviousData } from '@tanstack/react-query';
import axios from 'axios';
import type {
  UserListItem,
  UserFilters,
  PaginatedResponse,
} from '@/types/users';

/**
 * fetchUsers — Fetches paginated users list from the API.
 * Maps camelCase frontend filters to snake_case backend parameters.
 */
async function fetchUsers(
  filters: UserFilters = {},
): Promise<PaginatedResponse<UserListItem>> {
  const params = new URLSearchParams();
  
  if (filters.page) params.append('page', String(filters.page));
  if (filters.perPage) params.append('per_page', String(filters.perPage));
  if (filters.search) params.append('search', filters.search);
  if (filters.status) params.append('status', filters.status);
  if (filters.dateFrom) params.append('date_from', filters.dateFrom);
  if (filters.dateTo) params.append('date_to', filters.dateTo);
  if (filters.sortBy) params.append('sort_by', filters.sortBy);
  if (filters.sortDir) params.append('sort_dir', filters.sortDir);

  const { data } = await axios.get<PaginatedResponse<UserListItem>>(
    `/api/users?${params.toString()}`
  );
  return data;
}

/**
 * useUsers — TanStack Query hook for users listing.
 */
export function useUsers(filters: UserFilters) {
  return useQuery<PaginatedResponse<UserListItem>, Error>({
    queryKey: ['users', 'list', filters],
    queryFn: () => fetchUsers(filters),
    placeholderData: keepPreviousData,
    staleTime: 1000 * 60 * 2, // 2 minutes
  });
}
