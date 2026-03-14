import * as React from 'react';
import type { AuthUser } from '@/types/auth';

interface AuthContextValue {
  user: AuthUser | null;
  permissions: string[];
  roles: string[];
  isSuperAdmin: boolean;
}

interface AuthProviderProps {
  children: React.ReactNode;
  user: AuthUser | null;
}

const defaultValue: AuthContextValue = {
  user: null,
  permissions: [],
  roles: [],
  isSuperAdmin: false,
};

const AuthContext = React.createContext<AuthContextValue>(defaultValue);

export function AuthProvider({ children, user }: AuthProviderProps): React.JSX.Element {
  const value = React.useMemo<AuthContextValue>(() => {
    const roles = user?.roles ?? [];

    return {
      user,
      permissions: user?.permissions ?? [],
      roles,
      isSuperAdmin: roles.includes('SUPER_ADMIN'),
    };
  }, [user]);

  return <AuthContext.Provider value={value}>{children}</AuthContext.Provider>;
}

export function useAuthContext(): AuthContextValue {
  return React.useContext(AuthContext);
}
