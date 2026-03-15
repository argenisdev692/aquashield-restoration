<?php

declare(strict_types=1);

use Modules\AccessControl\Application\Support\PrivilegedAccess;

it('preserves the super admin role for non privileged actors', function (): void {
    $roles = PrivilegedAccess::sanitizeRoles(
        requestedRoles: ['MANAGER'],
        currentRoles: ['SUPER_ADMIN', 'MANAGER'],
        actorIsSuperAdmin: false,
    );

    expect($roles)->toContain('SUPER_ADMIN');
});

it('preserves protected permissions for non privileged actors', function (): void {
    $permissions = PrivilegedAccess::sanitizePermissions(
        requestedPermissions: ['EXPORT_REPORTS'],
        currentPermissions: ['CREATE_PERMISSION', 'READ_PERMISSION'],
        actorIsSuperAdmin: false,
    );

    expect($permissions)
        ->toContain('CREATE_PERMISSION')
        ->toContain('READ_PERMISSION')
        ->toContain('EXPORT_REPORTS');
});
