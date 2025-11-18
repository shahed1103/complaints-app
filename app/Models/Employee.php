<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Employee extends Model
{
    protected $fillable = [
        'name',
        'complaint_department_id',
        'user_id'
    ];

    public function complaintDepartment(){
        return $this->belongsTo(ComplaintDepartment::class, 'complaint_department_id');
    }

    public function user(){
        return $this->belongsTo(User::class, 'user_id');
    }

    public function notes(){
        return $this->hasMany(Note::class);
    }
}
