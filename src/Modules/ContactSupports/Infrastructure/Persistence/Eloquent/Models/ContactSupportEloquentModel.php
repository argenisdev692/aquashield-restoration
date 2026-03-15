<?php

declare(strict_types=1);

namespace Src\Modules\ContactSupports\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class ContactSupportEloquentModel extends Model
{
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'contact_supports';

    protected $fillable = [
        'uuid',
        'first_name',
        'last_name',
        'email',
        'phone',
        'message',
        'sms_consent',
        'readed',
    ];

    protected $casts = [
        'sms_consent' => 'boolean',
        'readed' => 'boolean',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly([
                'first_name',
                'last_name',
                'email',
                'phone',
                'message',
                'sms_consent',
                'readed',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('contact_supports.contact_support');
    }
}
