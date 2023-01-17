<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class Client extends Authenticatable implements JWTSubject
{
    use HasFactory;

    protected $fillable = [
        'nom',
        'prenom',
        'socite',
        'cri',
        'email',
        'cp',
        'adresse',
        'ville',
        'pays',
        'tva',
        'password',
        'gsm',
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
        return $this->hasMany('App\Models\Mission', 'client_id', 'id');
    }

    public function users()
    {
        return $this->belongsToMany('App\Models\User');
    }
}