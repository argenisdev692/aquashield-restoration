<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class ModelAI extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'name',
        'email',
        'type',
        'description',
        'api_key',
        'user_id',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        // Removed api_key from hidden so it can be displayed in CRUD
    ];

    /**
     * Get the user that owns the model AI.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
