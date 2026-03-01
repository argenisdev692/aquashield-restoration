<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class Portfolio extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'uuid',
        'project_type_id'
    ];

    protected $casts = [
        'order' => 'integer'
    ];

    protected static function boot()
    {
        parent::boot();
        
        static::creating(function ($model) {
            $model->uuid = (string) Str::uuid();
        });
    }

    public function projectType(): BelongsTo
    {
        return $this->belongsTo(ProjectType::class);
    }

    
    public function serviceCategory()
    {
        return $this->projectType->serviceCategory();
    }

    public function images()
    {
        return $this->hasMany(PortfolioImage::class);
    }
}
