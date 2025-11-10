<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{

    protected $fillable = ['user_id' , 'wallet_value' , 'wallet_password'];

    /* The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'wallet_password',
    ];
    
        /* The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'wallet_password' => 'hashed',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
