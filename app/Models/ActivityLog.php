<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ActivityLog extends Model
{
     protected $fillable = [
          'log_name',
          'description',
          'user_id',
         'ip_address'
    ];


    public function user()
{
    return $this->belongsTo(User::class, 'user_id');
}
}
