import axios from 'axios';
import type { UserDetail } from '@/types/users';

/**
 * fetchUser — Fetches a single user detail by UUID from the API.
 */
export async function fetchUser(uuid: string): Promise<UserDetail> {
  const { data } = await axios.get<{ data: UserDetail }>(`/api/users/${uuid}`);
  return data.data;
}
