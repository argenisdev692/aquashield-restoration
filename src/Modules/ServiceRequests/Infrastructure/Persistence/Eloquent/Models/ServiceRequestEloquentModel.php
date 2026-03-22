<?php

declare(strict_types=1);

namespace Src\Modules\ServiceRequests\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class ServiceRequestEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'service_requests';

    protected $fillable = [
        'uuid',
        'requested_service',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['requested_service'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('service_requests.service_request');
    }
}
