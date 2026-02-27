<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TypeDamage extends Model
{
    use HasFactory, SoftDeletes;
      protected $fillable = [
        'uuid',
        'type_damage_name',
        'description',
        'severity',
    ];

    public function claims()
    {
        return $this->hasMany(Claim::class, 'type_damage_id');
    }
}
