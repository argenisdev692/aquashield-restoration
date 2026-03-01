<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MortgageCompany extends Model
{
    use HasFactory, SoftDeletes;
    protected $fillable = [
        'uuid',
        'mortgage_company_name',
        'address',
        'phone',
        'email',
        'website',
        'user_id'
    ];

     public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function affidavits()
    {
        return $this->hasMany(AffidavitForm::class);
    }

    // Opcional: Si quieres acceder a todos los claims relacionados
    public function claims()
    {
        return $this->hasManyThrough(
            Claim::class,
            AffidavitForm::class,
            'mortgage_company_id', // Llave foránea en affidavit_forms
            'id', // Llave primaria en claims
            'id', // Llave primaria en mortgage_companies
            'claim_id' // Llave foránea en affidavit_forms que apunta a claims
        );
    }
}
