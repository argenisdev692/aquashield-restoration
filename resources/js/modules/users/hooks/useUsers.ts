import axios from 'axios';
import type {
  UserListItem,
  UserFilters,
  PaginatedResponse,
} from '@/types/users';

/**
 * fetchUsers — Fetches paginated users list from the API.
 */
export async function fetchUsers(
  filters: UserFilters = {},
): Promise<PaginatedResponse<UserListItem>> {
  const { data } = await axios.get<PaginatedResponse<UserListItem>>(
    '/api/users',
    { params: filters },
  );
  return data;
}
