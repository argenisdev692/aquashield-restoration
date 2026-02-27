import { usePage } from '@inertiajs/react';
import type { AuthUser, AuthPageProps } from '@/types/auth';

export function useCurrentUser(): AuthUser | null {
  const { auth } = usePage<AuthPageProps>().props;
  return auth.user;
}
