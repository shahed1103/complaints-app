<?php

namespace App\Models;
use App\Traits\LogsActivity;
use Illuminate\Database\Eloquent\Model;

class ComplaintVersion extends Model
{

    use LogsActivity;
        protected $fillable = [
        'complaint_type_id',
        'user_id',
        'complaint_department_id',
        'complaint_status_id',
        'problem_description',
        'location',
        'complaint_id',
        'editor_name',
        'editor_id',
        'editor_role',
        'what_edit',
        'note'
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

    public function additionalInfos() {
        return $this->hasMany(AdditionalInfo::class);
    }

    public function complaint() {
        return $this->belongsTo(Complaint::class , 'complaint_id');
    }

}

