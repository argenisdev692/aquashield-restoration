import axios from 'axios';
import type {
  CreateUserPayload,
  UpdateUserPayload,
  UserDetail,
} from '@/types/users';

/**
 * createUser — POST /api/users
 */
export async function createUser(
  payload: CreateUserPayload,
): Promise<UserDetail> {
  const { data } = await axios.post<{ data: UserDetail }>(
    '/api/users',
    payload,
  );
  return data.data;
}

/**
 * updateUser — PUT /api/users/{uuid}
 */
export async function updateUser(
  uuid: string,
  payload: UpdateUserPayload,
): Promise<UserDetail> {
  const { data } = await axios.put<{ data: UserDetail }>(
    `/api/users/${uuid}`,
    payload,
  );
  return data.data;
}

/**
 * deleteUser — DELETE /api/users/{uuid}
 */
export async function deleteUser(uuid: string): Promise<void> {
  await axios.delete(`/api/users/${uuid}`);
}

/**
 * bulkDeleteUsers — DELETE /api/users/{uuid} for multiple UUIDs
 */
export async function bulkDeleteUsers(uuids: string[]): Promise<void> {
  await Promise.all(uuids.map(uuid => axios.delete(`/api/users/${uuid}`)));
}
