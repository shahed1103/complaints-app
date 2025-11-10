<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
use HasApiTokens, HasFactory, Notifiable , HasRoles;

    /* The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'role_id',
        'name',
        'email',
        'password',
        'gender_id',
        'phone',
        'city_id',
        'age',
        'photo'
    ];

    /* The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /* The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];



    /* Get the user that owns the User
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role(): BelongsTo
    {
        return $this->belongsTo(Role::class);
    }

    public function notifications()
    {
        return $this->hasMany(Notification::class);
    }


    public function indCampaigns()
    {
        return $this->hasMany(IndCompaign::class);
    }

    public function donations()
    {
    return $this->hasMany(Donation::class);
    }


    public function donationAssociationCampaigns()
    {
    return $this->hasMany(DonationAssociationCampaign::class);
    }

    public function city()
    {
        return $this->belongsTo(City::class);
    }

    public function gender()
    {
        return $this->belongsTo(Gender::class);
    }

    public function volunteerProfile()
    {
    return $this->hasOne(VolunteerProfile::class , 'user_id');
    }

        public function associations()
    {
        return $this->hasMany(Association::class, 'user_id');
    }

    public function wallet()
    {
        return $this->hasOne(Wallet::class);
    }

    public function addedInkindDonations() {
    return $this->hasMany(InkindDonation::class, 'owner_id');
    }

    public function reservedInkindDonations() {
        return $this->belongsToMany(
            InkindDonation::class,
            'inkind_donation_reservations',
            'user_id',
            'inkind_donation_id'
        );
    }

    public function reservations() {
        return $this->hasMany(InkindDonationReservation::class, 'user_id');
    }
}
