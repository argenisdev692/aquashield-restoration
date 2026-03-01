<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ServiceCategory extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'type',
        'category',
        'user_id'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function projectTypes(): HasMany
    {
        return $this->hasMany(ProjectType::class);
    }

    public function portfolios()
    {
        return $this->hasManyThrough(Portfolio::class, ProjectType::class);
    }
}
