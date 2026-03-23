<?php

declare(strict_types=1);

namespace Src\Modules\FilesEsx\Infrastructure\Persistence\Eloquent\Models;

use Database\Factories\FileEsxEloquentModelFactory;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Modules\Users\Infrastructure\Persistence\Eloquent\Models\UserEloquentModel;
use Spatie\Activitylog\LogOptions;
use Spatie\Activitylog\Traits\LogsActivity;

/**
 * FileEsxEloquentModel
 *
 * @internal — Infrastructure only. Use FileEsxRepositoryPort.
 */
final class FileEsxEloquentModel extends Model
{
    use HasFactory;
    use LogsActivity;

    protected $table = 'files_esxes';

    protected static function newFactory(): FileEsxEloquentModelFactory
    {
        return FileEsxEloquentModelFactory::new();
    }

    protected $fillable = [
        'uuid',
        'file_name',
        'file_path',
        'uploaded_by',
    ];

    public function getActivitylogOptions(): LogOptions
    {
        return LogOptions::defaults()
            ->logOnly(['file_name', 'file_path', 'uploaded_by'])
            ->logOnlyDirty()
            ->dontSubmitEmptyLogs()
            ->useLogName('crm.files_esx');
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(UserEloquentModel::class, 'uploaded_by');
    }

    public function assignments(): HasMany
    {
        return $this->hasMany(FileAssignmentEloquentModel::class, 'file_id');
    }

    public function assignedAdjusters(): BelongsToMany
    {
        return $this->belongsToMany(
            UserEloquentModel::class,
            'file_assignment_esxes',
            'file_id',
            'public_adjuster_id',
        );
    }

    public function scopeSearch(Builder $query, ?string $search): Builder
    {
        return $query->when($search, static function (Builder $builder, string $term): void {
            $builder->where(static function (Builder $nested) use ($term): void {
                $nested->where('file_name', 'like', "%{$term}%")
                    ->orWhere('file_path', 'like', "%{$term}%")
                    ->orWhereHas('uploader', static function (Builder $user) use ($term): void {
                        $user->where('name', 'like', "%{$term}%");
                    });
            });
        });
    }

    public function scopeInDateRange(Builder $query, ?string $from, ?string $to): Builder
    {
        return $query
            ->when($from, fn (Builder $builder): Builder => $builder->whereDate('created_at', '>=', $from))
            ->when($to, fn (Builder $builder): Builder => $builder->whereDate('created_at', '<=', $to));
    }
}
