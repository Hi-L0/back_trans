<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Contracts\Auth\MustVerifyEmail;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'nom',
        'prenom',
        'societe',
        'email',
        'password',
        'gsm',
        'adresse',
        'ville',
        'pays',
        'cp',
        'avatar',

    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];


    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getJWTIdentifier()
    {
        return $this->getKey();
    }


    /**
     * Return a key value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */

    public function getJWTCustomClaims()
    {
        return [];
    }

    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }

    public function clients()
    {
        return $this->belongsToMany('App\Models\Client');
    }
    public function missions()
    {
        return $this->hasManyThrough('App\Models\Mission', 'App\Models\Agent', 'supervisor', 'user_id', 'id', 'id')->orderBy('created_at', 'DESC');
    }
    public function missions_inprogress()
    {
        return $this->missions()->where('etat', '!=', 4)->orderBy('created_at', 'DESC');
    }
    public function finishedMissions()
    {
        return $this->missions()->where('etat', 4)->orderBy('created_at', 'DESC');
    }
    public function trashedMissions()
    {
        return $this->missions()->onlyTrashed();
    }
    public function factures()
    {
        return $this->hasMany('App\Models\Facture', 'owner', 'id');
    }
    public function closedFactures()
    {
        return $this->factures()->where('isClosed', true)->orderBy('created_at', 'DESC');
    }
    public function PaidFactures()
    {
        return $this->closedFactures()->where('isPaid', true)->orderBy('created_at', 'DESC');
    }
    public function notPaidFactures()
    {
        return $this->closedFactures()->where('isPaid', false)->orderBy('created_at', 'DESC');
    }
    public function sendPasswordResetNotification($token)
    {
        $url = env('EP_URL') . '/auth/reset-password/' . $token;  //(EP) end point url custom env variable
        $this->notify(new ResetPasswordNotification($url));
    }
}