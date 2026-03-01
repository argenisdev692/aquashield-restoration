<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class ProjectType extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'title',
        'description',
        'status',
        'user_id',
        'service_category_id'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function portfolios(): HasMany
    {
        return $this->hasMany(Portfolio::class);
    }
    
    public function serviceCategory(): BelongsTo
    {
        return $this->belongsTo(ServiceCategory::class);
    }
    
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
} 