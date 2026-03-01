<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Seo extends Model
{
    use HasFactory;

    protected $table = 'seo';

    protected $fillable = [
        'description',
        'title',
        'image',
        'author',
        'robots',
        'canonical_url',
    ];

    /**
     * Get the parent model (post, category, etc).
     */
    public function model()
    {
        return $this->morphTo();
    }
} 