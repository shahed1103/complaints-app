<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ComplaintDepartment extends Model
{

    protected $fillable = [
        'department_name',
    ];

    public function complaintِs(){
        return $this->hasMany(Complaintِ::class);
    }

    public function employees(){
        return $this->hasMany(Employee::class);
    }
}
