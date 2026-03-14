import { useAuthContext } from '@/modules/auth/context/AuthContext';
import type { AuthUser } from '@/types/auth';

export function useCurrentUser(): AuthUser | null {
  return useAuthContext().user;
}
