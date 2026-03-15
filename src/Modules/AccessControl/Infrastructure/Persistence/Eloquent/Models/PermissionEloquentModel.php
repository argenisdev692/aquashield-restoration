<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Permission;

final class PermissionEloquentModel extends Permission
{
    use LogsActivity;

    protected $table = 'permissions';

    protected $fillable = [
        'uuid',
        'name',
        'guard_name',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $permission): void {
            if (! is_string($permission->uuid) || $permission->uuid === '') {
                $permission->uuid = (string) Str::uuid();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('permissions')
            ->logOnly([
                'uuid',
                'name',
                'guard_name',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
