<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DocumentTemplateAdjuster extends Model
{
    use HasFactory;

    protected $fillable = [
        'uuid',
        'template_description_adjuster',
        'template_type_adjuster',
        'template_path_adjuster',
        'public_adjuster_id',
        'uploaded_by', 
    ];

    /**
     * Obtiene el usuario que subió el documento.
     */
    public function uploadedBy()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    /**
     * Obtiene el adjuster asociado al documento.
     */
    public function adjuster()
    {
    return $this->belongsTo(User::class, 'public_adjuster_id');
    }

}
