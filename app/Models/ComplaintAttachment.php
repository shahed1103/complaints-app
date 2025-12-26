<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintAttachment extends Model
{
        protected $fillable = [
        'attachment',
        'complaint_id',
        'complaint_version_id'
    ];

    public function complaint(){
        return $this->belongsTo(Complaint::class, 'complaint_id');

    }


    public function complaintVersion(){
        return $this->belongsTo(CopmlaintVersion::class, 'complaint_version_id');

    }
}
