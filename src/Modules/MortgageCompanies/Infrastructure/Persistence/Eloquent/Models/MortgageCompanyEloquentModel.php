<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/** @internal */
final class MortgageCompanyEloquentModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    use LogsActivity;

    protected $table = 'mortgage_companies';

    protected $fillable = [
        'uuid',
        'mortgage_company_name',
        'address',
        'phone',
        'email',
        'website',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['mortgage_company_name', 'address', 'phone', 'email', 'website'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('mortgage_companies');
    }

    public function user()
    {
        return $this->belongsTo(UserEloquentModel::class);
    }
}
