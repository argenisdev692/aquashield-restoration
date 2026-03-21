<?php

declare(strict_types=1);

namespace Modules\MortgageCompanies\Infrastructure\Persistence\Eloquent\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

final class MortgageCompanyEloquentModel extends Model
{
    use HasFactory;
    use LogsActivity;
    use SoftDeletes;

    protected $table = 'mortgage_companies';

    protected $fillable = [
        'uuid',
        'mortgage_company_name',
        'address',
        'address_2',
        'phone',
        'email',
        'website',
        'user_id',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->useLogName('mortgage_company')
            ->logOnly([
                'mortgage_company_name',
                'address',
                'address_2',
                'phone',
                'email',
                'website',
                'user_id',
            ])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs();
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'user_id');
    }
}
