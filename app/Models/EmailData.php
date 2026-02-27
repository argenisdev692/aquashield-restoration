<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class EmailData extends Model
{
    use SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'uuid',
        'description',
        'email',
        'phone',
        'type',
        'user_id',
    ];

    /**
     * Get the user that owns the email data.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
