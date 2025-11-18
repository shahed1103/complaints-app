<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Note extends Model
{
    protected $fillable = [
        'note',
        'complaint_id',
        'employee_id'
    ];

    public function complaint(){
        return $this->belongsTo(Complaint::class, 'complaint_id');
    }

    public function employee(){
        return $this->belongsTo(Complaint::class, 'employee_id');
    }
}
