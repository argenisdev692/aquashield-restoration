<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Modules\PublicCompanies\Infrastructure\Persistence\Eloquent\Models\PublicCompanyEloquentModel;

class PublicAdjuster extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'uuid',
        'user_id',
        'public_company_id',
        
    ];

     public function user()
    {
        return $this->belongsTo(User::class,'user_id');
    }


    
    public function publicCompany()
    {
        return $this->belongsTo(PublicCompanyEloquentModel::class,'public_company_id');
    }

      public function publicAdjusterAssignments()
    {
        return $this->hasMany(PublicAdjusterAssignment::class);
    }


}
