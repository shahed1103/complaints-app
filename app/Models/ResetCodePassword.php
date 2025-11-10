<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

//use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class ResetCodePassword extends Model
{

    use HasApiTokens, HasFactory ;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'email',
        'code',
        'created_at',
    ];
}
