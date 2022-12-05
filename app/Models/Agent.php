<?php

namespace App\Models;

use App\Notifications\ResetPasswordNotification;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Agent extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    // protected $guard = 'agents';
    protected $fillable = [
        'name',
        'nom',
        'prenom',
        'supervisor',
        'email',
        'societe',
        'gsm',
        'adresse',
        'ville',
        'pays',
        'cp',
        'is_commis',
        'password',
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

    public function missions()
    {
        return $this->hasMany('App\Models\Mission');
    }
    public function manager()
    {
        return $this->belongsTo('App\Models\User', 'supervisor');
    }
    public function roles()
    {
        return $this->belongsToMany('App\Models\Role');
    }
    public function sendPasswordResetNotification($token)
    {
        $url = 'http://127.0.0.1:3000/auth/reset-password/' . $token;
        $this->notify(new ResetPasswordNotification($url));
    }
}