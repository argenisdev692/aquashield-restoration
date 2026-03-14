/* ══════════════════════════════════════════════════════════════════
   PermissionGuard — Conditional rendering by role/permission
   Per ARQUITECTURE-REACT-INERTIA.md — modules/auth/components/
   ══════════════════════════════════════════════════════════════════ */
import { useAuthContext } from '@/modules/auth/context/AuthContext';

interface PermissionGuardProps {
  roles?: string[];
  /** Required permission(s) — user must have at least one */
  permissions?: string[];
  /** Content to render if authorized */
  children: React.ReactNode;
  /** Optional fallback when unauthorized (defaults to null) */
  fallback?: React.ReactNode;
}

export function PermissionGuard({
  roles,
  permissions,
  children,
  fallback = null,
}: PermissionGuardProps): React.JSX.Element | null {
  const { user, permissions: userPermissions, roles: userRoles, isSuperAdmin } = useAuthContext();

  if (!user) {
    return fallback as React.JSX.Element | null;
  }

  if (isSuperAdmin) {
    return children as React.JSX.Element;
  }

  if (roles && roles.length > 0) {
    const hasRole = roles.some((role) => userRoles.includes(role));
    if (!hasRole) {
      return fallback as React.JSX.Element | null;
    }
  }

  if (!permissions || permissions.length === 0) {
    return children as React.JSX.Element;
  }

  // Check permission match (at least one)
  const hasPermission = permissions.some((perm) => userPermissions.includes(perm));
  if (!hasPermission) {
    return fallback as React.JSX.Element | null;
  }

  return children as React.JSX.Element;
}
