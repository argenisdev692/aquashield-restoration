<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class ScopeSheet extends Model
{
    use HasFactory; use SoftDeletes;

     protected $fillable = [
        'uuid',
        'claim_id',
        'scope_sheet_description',
        'generated_by',
    ];

     public function claim()
    {
        return $this->belongsTo(Claim::class);
    }

    public function presentations()
    {
        return $this->hasMany(ScopeSheetPresentation::class);
    }

    public function zones()
    {
        return $this->hasMany(ScopeSheetZone::class);
    }

    public function generatedBy()
    {
        return $this->belongsTo(User::class, 'generated_by');
    }

     public function scopeSheetExport()
    {
        return $this->hasOne(ScopeSheetExport::class);
    }
}
