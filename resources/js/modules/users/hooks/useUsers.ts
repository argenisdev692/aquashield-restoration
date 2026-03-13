import { useQuery } from '@tanstack/react-query';
import axios from 'axios';
import { keepPreviousData } from '@tanstack/react-query';
import type { UserFilters, UserListItem, PaginatedResponse } from '@/modules/users/types';

/**
 * useUsers — Returns a list of users filtered by the provided filters.
 */
export const useUsers = (filters: UserFilters = {}) => {
  return useQuery<PaginatedResponse<UserListItem>, Error>({
    queryKey: ['users', filters],
    queryFn: async () => {
      const { data } = await axios.get<PaginatedResponse<UserListItem>>('/users/data/admin', {
        params: filters,
      });
      return data;
    },
    placeholderData: keepPreviousData,
  });
};
