<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Complaint extends Model
{
    protected $fillable = [
        'complaint_type_id',
        'user_id',
        'complaint_department_id',
        'complaint_status_id',
        'problem_description',
        'location'
    ];
    
    public function complaintType(){
        return $this->belongsTo(ComplaintType::class, 'complaint_type_id');
    }

    public function complaintAttachments(){
        return $this->hasMany(ComplaintAttachment::class);
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function complaintDepartment(){
        return $this->belongsTo(ComplaintDepartment::class, 'complaint_department_id');
    }

    public function complaintStatus(){
        return $this->belongsTo(ComplaintStatus::class, 'complaint_status_id');
    }

    public function notes(){
        return $this->hasMany(Note::class);
    }

}

