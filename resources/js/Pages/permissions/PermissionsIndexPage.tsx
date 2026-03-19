import * as React from 'react';
import { Head, usePage } from '@inertiajs/react';
import AppLayout from '@/pages/layouts/AppLayout';
import { PermissionGuard } from '@/modules/auth/components/PermissionGuard';
import { useAccessRoles, usePermissionCatalog, useUserAccess, useUserSearch } from '@/modules/access-control/hooks/useAccessControl';
import { useAccessControlMutations } from '@/modules/access-control/hooks/useAccessControlMutations';
import type { AccessRoleItem, PermissionCatalogItem, UserSearchResult } from '@/modules/access-control/types';
import type { AuthPageProps } from '@/types/auth';
import { Search, ShieldCheck, UserCog, KeyRound } from 'lucide-react';
import axios from 'axios';

type AccessTab = 'catalog' | 'roles' | 'users';

const TAB_BUTTON_CLASS = 'inline-flex items-center gap-2 rounded-xl border border-(--border-default) px-4 py-2 text-sm font-semibold transition-all';

const PROTECTED_PERMISSION_NAMES = [
  'CREATE_ROLE',
  'READ_ROLE',
  'UPDATE_ROLE',
  'DELETE_ROLE',
  'RESTORE_ROLE',
  'CREATE_PERMISSION',
  'READ_PERMISSION',
  'UPDATE_PERMISSION',
  'DELETE_PERMISSION',
  'RESTORE_PERMISSION',
] as const;

function normalizePermissionName(value: string): string {
  return value.toUpperCase().replace(/[^A-Z0-9_]/g, '_').replace(/_{2,}/g, '_').replace(/^_+|_+$/g, '');
}

function getValidationMessage(error: unknown, field: string): string {
  if (!axios.isAxiosError(error)) {
    return '';
  }

  const errors = error.response?.data as { errors?: Record<string, string[]> } | undefined;
  return errors?.errors?.[field]?.[0] ?? '';
}

function toggleValue(values: string[], value: string): string[] {
  return values.includes(value)
    ? values.filter((item) => item !== value)
    : [...values, value];
}

export default function PermissionsIndexPage(): React.JSX.Element {
  const { auth } = usePage<AuthPageProps>().props;
  const currentPermissions = auth.user?.permissions ?? [];
  const isSuperAdmin = (auth.user?.roles ?? []).includes('SUPER_ADMIN');
  const [activeTab, setActiveTab] = React.useState<AccessTab>('catalog');
  const [permissionSearch, setPermissionSearch] = React.useState<string>('');
  const [newPermissionName, setNewPermissionName] = React.useState<string>('');
  const [permissionErrors, setPermissionErrors] = React.useState<Record<string, string>>({});
  const [selectedRoleUuid, setSelectedRoleUuid] = React.useState<string>('');
  const [selectedRolePermissions, setSelectedRolePermissions] = React.useState<string[]>([]);
  const [userSearch, setUserSearch] = React.useState<string>('');
  const [selectedUser, setSelectedUser] = React.useState<UserSearchResult | null>(null);
  const [selectedUserRoles, setSelectedUserRoles] = React.useState<string[]>([]);
  const [selectedUserPermissions, setSelectedUserPermissions] = React.useState<string[]>([]);

  const hasPermissionCatalogAccess = isSuperAdmin || currentPermissions.includes('READ_PERMISSION');
  const hasRoleTabAccess = hasPermissionCatalogAccess;
  const hasUserTabAccess = isSuperAdmin
    || currentPermissions.includes('VIEW_USERS')
    || currentPermissions.includes('UPDATE_USERS')
    || currentPermissions.includes('READ_PERMISSION')
    || currentPermissions.includes('UPDATE_PERMISSION');

  const permissionCatalogQuery = usePermissionCatalog(permissionSearch, hasPermissionCatalogAccess || hasUserTabAccess);
  const accessRolesQuery = useAccessRoles(hasRoleTabAccess || hasUserTabAccess);
  const userSearchQuery = useUserSearch(userSearch, activeTab === 'users' && hasUserTabAccess);
  const userAccessQuery = useUserAccess(selectedUser?.uuid ?? null, activeTab === 'users' && hasUserTabAccess);
  const { createPermission, syncRolePermissions, syncUserAccess } = useAccessControlMutations();

  const permissions = Array.isArray(permissionCatalogQuery.data?.data) ? permissionCatalogQuery.data.data : [];
  const roles = Array.isArray(accessRolesQuery.data?.data) ? accessRolesQuery.data.data : [];
  const searchedUsers = Array.isArray(userSearchQuery.data?.data) ? userSearchQuery.data.data : [];
  const userAccess = userAccessQuery.data?.data;
  const accessControlErrorMessage = permissionCatalogQuery.error?.message
    ?? accessRolesQuery.error?.message
    ?? userSearchQuery.error?.message
    ?? userAccessQuery.error?.message
    ?? null;
  const visiblePermissions = React.useMemo<PermissionCatalogItem[]>(
    () => (isSuperAdmin ? permissions : permissions.filter((permission) => !PROTECTED_PERMISSION_NAMES.includes(permission.name as typeof PROTECTED_PERMISSION_NAMES[number]))),
    [isSuperAdmin, permissions],
  );
  const visibleRoles = React.useMemo<AccessRoleItem[]>(
    () => (isSuperAdmin ? roles : roles.filter((role) => role.name !== 'SUPER_ADMIN')),
    [isSuperAdmin, roles],
  );

  const selectedRole = React.useMemo<AccessRoleItem | undefined>(
    () => visibleRoles.find((role) => role.uuid === selectedRoleUuid),
    [visibleRoles, selectedRoleUuid],
  );

  React.useEffect(() => {
    if (visibleRoles.length === 0) {
      setSelectedRoleUuid('');
      return;
    }

    if (!selectedRoleUuid) {
      setSelectedRoleUuid(visibleRoles[0]?.uuid ?? '');
      return;
    }

    if (!visibleRoles.some((role) => role.uuid === selectedRoleUuid)) {
      setSelectedRoleUuid(visibleRoles[0]?.uuid ?? '');
    }
  }, [visibleRoles, selectedRoleUuid]);

  React.useEffect(() => {
    const availableTabs: AccessTab[] = [];

    if (hasPermissionCatalogAccess) {
      availableTabs.push('catalog');
    }

    if (hasRoleTabAccess) {
      availableTabs.push('roles');
    }

    if (hasUserTabAccess) {
      availableTabs.push('users');
    }

    if (availableTabs.length > 0 && !availableTabs.includes(activeTab)) {
      setActiveTab(availableTabs[0]);
    }
  }, [activeTab, hasPermissionCatalogAccess, hasRoleTabAccess, hasUserTabAccess]);

  React.useEffect(() => {
    if (!selectedRole) {
      setSelectedRolePermissions([]);
      return;
    }

    setSelectedRolePermissions(
      isSuperAdmin
        ? selectedRole.permission_names
        : selectedRole.permission_names.filter((permissionName) => !PROTECTED_PERMISSION_NAMES.includes(permissionName as typeof PROTECTED_PERMISSION_NAMES[number])),
    );
  }, [isSuperAdmin, selectedRole]);

  React.useEffect(() => {
    if (!userAccess) {
      return;
    }

    setSelectedUserRoles(isSuperAdmin ? userAccess.roles : userAccess.roles.filter((roleName) => roleName !== 'SUPER_ADMIN'));
    setSelectedUserPermissions(
      isSuperAdmin
        ? userAccess.direct_permissions
        : userAccess.direct_permissions.filter((permissionName) => !PROTECTED_PERMISSION_NAMES.includes(permissionName as typeof PROTECTED_PERMISSION_NAMES[number])),
    );
  }, [isSuperAdmin, userAccess]);

  async function handleCreatePermission(event: React.FormEvent<HTMLFormElement>): Promise<void> {
    event.preventDefault();
    setPermissionErrors({});

    try {
      await createPermission.mutateAsync({
        name: normalizePermissionName(newPermissionName),
      });
      setNewPermissionName('');
    } catch (error) {
      setPermissionErrors({
        name: getValidationMessage(error, 'name'),
      });
    }
  }

  async function handleSaveRolePermissions(): Promise<void> {
    if (!selectedRole) {
      return;
    }

    await syncRolePermissions.mutateAsync({
      uuid: selectedRole.uuid,
      payload: {
        permissions: selectedRolePermissions,
      },
    });
  }

  async function handleSaveUserAccess(): Promise<void> {
    if (!selectedUser) {
      return;
    }

    await syncUserAccess.mutateAsync({
      uuid: selectedUser.uuid,
      payload: {
        roles: selectedUserRoles,
        permissions: selectedUserPermissions,
      },
    });
  }

  return (
    <>
      <Head title="Permissions" />
      <AppLayout>
        <div className="flex flex-col gap-6 animate-in fade-in duration-300">
          <div>
            <h1 className="text-3xl font-extrabold tracking-tight text-(--text-primary)">Permissions & Access Control</h1>
            <p className="mt-1 text-sm font-medium text-(--text-muted)">
              Manage the permission catalog, role-permission matrix, and user-level overrides from one place.
            </p>
          </div>

          <div className="flex flex-wrap gap-3">
            {hasPermissionCatalogAccess ? (
              <button
                type="button"
                onClick={() => setActiveTab('catalog')}
                className={`${TAB_BUTTON_CLASS} ${activeTab === 'catalog' ? 'bg-(--accent-primary) text-(--text-primary)' : 'bg-(--bg-card) text-(--text-secondary) hover:bg-(--bg-hover)'}`}
              >
                <KeyRound size={16} />
                Catalog
              </button>
            ) : null}
            {hasRoleTabAccess ? (
              <button
                type="button"
                onClick={() => setActiveTab('roles')}
                className={`${TAB_BUTTON_CLASS} ${activeTab === 'roles' ? 'bg-(--accent-primary) text-(--text-primary)' : 'bg-(--bg-card) text-(--text-secondary) hover:bg-(--bg-hover)'}`}
              >
                <ShieldCheck size={16} />
                Role Permissions
              </button>
            ) : null}
            {hasUserTabAccess ? (
              <button
                type="button"
                onClick={() => setActiveTab('users')}
                className={`${TAB_BUTTON_CLASS} ${activeTab === 'users' ? 'bg-(--accent-primary) text-(--text-primary)' : 'bg-(--bg-card) text-(--text-secondary) hover:bg-(--bg-hover)'}`}
              >
                <UserCog size={16} />
                User Access
              </button>
            ) : null}
          </div>

          {accessControlErrorMessage ? (
            <div className="rounded-3xl border border-(--accent-error) bg-(--bg-card) px-5 py-4 text-sm text-(--text-primary)">
              Failed to load access control data. {accessControlErrorMessage}
            </div>
          ) : null}

          {!hasPermissionCatalogAccess && !hasRoleTabAccess && !hasUserTabAccess ? (
            <div className="rounded-3xl border border-(--border-default) bg-(--bg-card) px-5 py-4 text-sm text-(--text-muted)">
              You do not have access to the permissions workspace.
            </div>
          ) : null}

          {activeTab === 'catalog' && hasPermissionCatalogAccess && !accessControlErrorMessage && (
            <div className="grid gap-6 lg:grid-cols-[1.1fr,0.9fr]">
              <div className="card-modern rounded-3xl border border-(--border-default) p-6 shadow-xl">
                <div className="mb-4 flex items-center gap-3 rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3">
                  <Search size={18} className="text-(--text-disabled)" />
                  <input
                    type="text"
                    value={permissionSearch}
                    onChange={(event) => setPermissionSearch(event.target.value)}
                    placeholder="Search permissions..."
                    className="flex-1 bg-transparent text-sm text-(--text-primary) outline-none placeholder:text-(--text-disabled)"
                  />
                </div>

                <div className="grid gap-3">
                  {permissionCatalogQuery.isPending ? (
                    <div className="rounded-2xl border border-(--border-default) px-4 py-8 text-sm text-(--text-muted)">Loading permissions...</div>
                  ) : visiblePermissions.length === 0 ? (
                    <div className="rounded-2xl border border-(--border-default) px-4 py-8 text-sm text-(--text-muted)">No permissions found.</div>
                  ) : (
                    visiblePermissions.map((permission: PermissionCatalogItem) => (
                      <div
                        key={permission.uuid}
                        className="flex flex-col gap-2 rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-4 sm:flex-row sm:items-center sm:justify-between"
                      >
                        <div>
                          <p className="text-sm font-semibold text-(--text-primary)">{permission.name}</p>
                          <p className="text-xs text-(--text-muted)">Guard: {permission.guard_name}</p>
                        </div>
                        <div className="text-right">
                          <p className="text-sm font-semibold text-(--text-primary)">{permission.roles_count}</p>
                          <p className="text-xs text-(--text-muted)">roles linked</p>
                        </div>
                      </div>
                    ))
                  )}
                </div>
              </div>

              <PermissionGuard permissions={['CREATE_PERMISSION']}>
                <form onSubmit={handleCreatePermission} className="card-modern flex flex-col gap-4 rounded-3xl border border-(--border-default) p-6 shadow-xl">
                  <div>
                    <h2 className="text-xl font-bold text-(--text-primary)">Create Permission</h2>
                    <p className="mt-1 text-sm text-(--text-muted)">Add a new permission to the catalog using uppercase snake case.</p>
                  </div>

                  <div className="flex flex-col gap-2">
                    <label htmlFor="permission-name" className="text-sm font-semibold text-(--text-primary)">
                      Permission name
                    </label>
                    <input
                      id="permission-name"
                      type="text"
                      value={newPermissionName}
                      onChange={(event) => {
                        setNewPermissionName(normalizePermissionName(event.target.value));
                        setPermissionErrors((previous) => ({ ...previous, name: '' }));
                      }}
                      placeholder="UPDATE_ROLE"
                      className="rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3 text-sm text-(--text-primary) outline-none transition-all placeholder:text-(--text-disabled) focus:border-(--accent-primary)"
                    />
                    {permissionErrors.name ? <p className="text-xs font-medium text-(--accent-error)">{permissionErrors.name}</p> : null}
                  </div>

                  <button
                    type="submit"
                    disabled={createPermission.isPending}
                    className="btn-modern btn-modern-primary inline-flex items-center justify-center gap-2 px-4 py-2 disabled:cursor-not-allowed disabled:opacity-60"
                  >
                    <KeyRound size={16} />
                    <span>{createPermission.isPending ? 'Saving...' : 'Create Permission'}</span>
                  </button>
                </form>
              </PermissionGuard>
            </div>
          )}

          {activeTab === 'roles' && hasRoleTabAccess && (
            <div className="grid gap-6 lg:grid-cols-[0.9fr,1.1fr]">
              <div className="card-modern rounded-3xl border border-(--border-default) p-6 shadow-xl">
                <h2 className="mb-4 text-xl font-bold text-(--text-primary)">Roles</h2>
                <div className="grid gap-3">
                  {visibleRoles.map((role) => {
                    const isSelected = role.uuid === selectedRoleUuid;

                    return (
                      <button
                        key={role.uuid}
                        type="button"
                        onClick={() => setSelectedRoleUuid(role.uuid)}
                        className={`rounded-2xl border px-4 py-4 text-left transition-all ${
                          isSelected
                            ? 'border-(--accent-primary) bg-(--bg-hover)'
                            : 'border-(--border-default) bg-(--bg-card) hover:bg-(--bg-hover)'
                        }`}
                      >
                        <p className="text-sm font-semibold text-(--text-primary)">{role.name}</p>
                        <p className="mt-1 text-xs text-(--text-muted)">{role.permissions_count} permissions linked</p>
                      </button>
                    );
                  })}
                </div>
              </div>

              <div className="card-modern rounded-3xl border border-(--border-default) p-6 shadow-xl">
                <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <h2 className="text-xl font-bold text-(--text-primary)">{selectedRole?.name ?? 'Select a role'}</h2>
                    <p className="text-sm text-(--text-muted)">Choose the permissions that this role should grant.</p>
                  </div>
                  <PermissionGuard permissions={['UPDATE_ROLE', 'UPDATE_PERMISSION']}>
                    <button
                      type="button"
                      onClick={handleSaveRolePermissions}
                      disabled={!selectedRole || syncRolePermissions.isPending}
                      className="btn-modern btn-modern-primary px-4 py-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {syncRolePermissions.isPending ? 'Saving...' : 'Save Role Permissions'}
                    </button>
                  </PermissionGuard>
                </div>

                <div className="grid gap-3 md:grid-cols-2">
                  {visiblePermissions.map((permission) => (
                    <label
                      key={permission.uuid}
                      className="flex items-start gap-3 rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3"
                    >
                      <input
                        type="checkbox"
                        checked={selectedRolePermissions.includes(permission.name)}
                        onChange={() => setSelectedRolePermissions((current) => toggleValue(current, permission.name))}
                        className="mt-1 h-4 w-4 rounded border-(--border-default)"
                      />
                      <div>
                        <p className="text-sm font-semibold text-(--text-primary)">{permission.name}</p>
                        <p className="text-xs text-(--text-muted)">Assigned to {permission.roles_count} roles</p>
                      </div>
                    </label>
                  ))}
                </div>
              </div>
            </div>
          )}

          {activeTab === 'users' && hasUserTabAccess && (
            <div className="grid gap-6 lg:grid-cols-[0.9fr,1.1fr]">
              <div className="card-modern rounded-3xl border border-(--border-default) p-6 shadow-xl">
                <h2 className="mb-4 text-xl font-bold text-(--text-primary)">Find User</h2>
                <div className="mb-4 flex items-center gap-3 rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3">
                  <Search size={18} className="text-(--text-disabled)" />
                  <input
                    type="text"
                    value={userSearch}
                    onChange={(event) => setUserSearch(event.target.value)}
                    placeholder="Search by name or email..."
                    className="flex-1 bg-transparent text-sm text-(--text-primary) outline-none placeholder:text-(--text-disabled)"
                  />
                </div>

                <div className="grid gap-3">
                  {searchedUsers.map((user) => {
                    const isSelected = selectedUser?.uuid === user.uuid;

                    return (
                      <button
                        key={user.uuid}
                        type="button"
                        onClick={() => setSelectedUser(user)}
                        className={`rounded-2xl border px-4 py-4 text-left transition-all ${
                          isSelected
                            ? 'border-(--accent-primary) bg-(--bg-hover)'
                            : 'border-(--border-default) bg-(--bg-card) hover:bg-(--bg-hover)'
                        }`}
                      >
                        <p className="text-sm font-semibold text-(--text-primary)">{user.name}</p>
                        <p className="mt-1 text-xs text-(--text-muted)">{user.email ?? 'No email available'}</p>
                      </button>
                    );
                  })}
                </div>
              </div>

              <div className="card-modern rounded-3xl border border-(--border-default) p-6 shadow-xl">
                <div className="mb-4 flex flex-col gap-2 sm:flex-row sm:items-center sm:justify-between">
                  <div>
                    <h2 className="text-xl font-bold text-(--text-primary)">{selectedUser?.name ?? 'Select a user'}</h2>
                    <p className="text-sm text-(--text-muted)">Assign roles and direct permissions carefully. `SUPER_ADMIN` stays protected.</p>
                  </div>
                  <PermissionGuard permissions={['UPDATE_USERS', 'UPDATE_PERMISSION']}>
                    <button
                      type="button"
                      onClick={handleSaveUserAccess}
                      disabled={!selectedUser || syncUserAccess.isPending}
                      className="btn-modern btn-modern-primary px-4 py-2 disabled:cursor-not-allowed disabled:opacity-60"
                    >
                      {syncUserAccess.isPending ? 'Saving...' : 'Save User Access'}
                    </button>
                  </PermissionGuard>
                </div>

                {!selectedUser ? (
                  <div className="rounded-2xl border border-(--border-default) px-4 py-8 text-sm text-(--text-muted)">Search and select a user to manage their access.</div>
                ) : userAccessQuery.isPending ? (
                  <div className="rounded-2xl border border-(--border-default) px-4 py-8 text-sm text-(--text-muted)">Loading user access...</div>
                ) : (
                  <div className="grid gap-6">
                    <div>
                      <h3 className="mb-3 text-sm font-bold uppercase tracking-wider text-(--text-muted)">Roles</h3>
                      <div className="grid gap-3 md:grid-cols-2">
                        {visibleRoles.map((role) => (
                          <label
                            key={role.uuid}
                            className="flex items-start gap-3 rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3"
                          >
                            <input
                              type="checkbox"
                              checked={selectedUserRoles.includes(role.name)}
                              onChange={() => setSelectedUserRoles((current) => toggleValue(current, role.name))}
                              className="mt-1 h-4 w-4 rounded border-(--border-default)"
                            />
                            <div>
                              <p className="text-sm font-semibold text-(--text-primary)">{role.name}</p>
                              <p className="text-xs text-(--text-muted)">{role.permissions_count} permissions from role</p>
                            </div>
                          </label>
                        ))}
                      </div>
                    </div>

                    <div>
                      <h3 className="mb-3 text-sm font-bold uppercase tracking-wider text-(--text-muted)">Direct permissions</h3>
                      <div className="grid gap-3 md:grid-cols-2">
                        {visiblePermissions.map((permission) => (
                          <label
                            key={permission.uuid}
                            className="flex items-start gap-3 rounded-2xl border border-(--border-default) bg-(--bg-card) px-4 py-3"
                          >
                            <input
                              type="checkbox"
                              checked={selectedUserPermissions.includes(permission.name)}
                              onChange={() => setSelectedUserPermissions((current) => toggleValue(current, permission.name))}
                              className="mt-1 h-4 w-4 rounded border-(--border-default)"
                            />
                            <div>
                              <p className="text-sm font-semibold text-(--text-primary)">{permission.name}</p>
                              <p className="text-xs text-(--text-muted)">Direct override permission</p>
                            </div>
                          </label>
                        ))}
                      </div>
                    </div>

                    <div className="rounded-2xl border border-(--border-default) bg-(--bg-subtle) px-4 py-4">
                      <h3 className="mb-2 text-sm font-bold uppercase tracking-wider text-(--text-muted)">Effective permissions</h3>
                      <div className="flex flex-wrap gap-2">
                        {(userAccess?.effective_permissions ?? []).map((permission) => (
                          <span
                            key={permission}
                            className="rounded-full border border-(--border-default) bg-(--bg-card) px-3 py-1 text-xs font-semibold text-(--text-secondary)"
                          >
                            {permission}
                          </span>
                        ))}
                      </div>
                    </div>
                  </div>
                )}
              </div>
            </div>
          )}
        </div>
      </AppLayout>
    </>
  );
}
