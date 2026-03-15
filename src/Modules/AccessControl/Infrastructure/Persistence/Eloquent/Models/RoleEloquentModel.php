<?php

declare(strict_types=1);

namespace Modules\AccessControl\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;
use Spatie\Permission\Models\Role;

final class RoleEloquentModel extends Role
{
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'roles';

    protected $fillable = [
        'uuid',
        'name',
        'guard_name',
    ];

    protected static function booted(): void
    {
        static::creating(static function (self $role): void {
            if (! is_string($role->uuid) || $role->uuid === '') {
                $role->uuid = (string) Str::uuid();
            }
        });
    }

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('roles')
            ->logOnly([
                'uuid',
                'name',
                'guard_name',
                'deleted_at',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }
}
