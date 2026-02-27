/* ══════════════════════════════════════════════════════════════════
   PermissionGuard — Conditional rendering by role/permission
   Per ARQUITECTURE-REACT-INERTIA.md — modules/auth/components/
   ══════════════════════════════════════════════════════════════════ */
import { usePage } from '@inertiajs/react';

interface PermissionGuardProps {
  /** Required role(s) — user must have at least one */
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
  const { auth } = usePage().props;

  if (!auth.user) {
    return fallback as React.JSX.Element | null;
  }

  const userRoles: string[] = (auth.user as { roles?: string[] }).roles ?? [];
  const userPermissions: string[] = (auth.user as { permissions?: string[] }).permissions ?? [];

  // Check role match (at least one)
  if (roles && roles.length > 0) {
    const hasRole = roles.some((role) => userRoles.includes(role));
    if (!hasRole) {
      return fallback as React.JSX.Element | null;
    }
  }

  // Check permission match (at least one)
  if (permissions && permissions.length > 0) {
    const hasPermission = permissions.some((perm) => userPermissions.includes(perm));
    if (!hasPermission) {
      return fallback as React.JSX.Element | null;
    }
  }

  return children as React.JSX.Element;
}
