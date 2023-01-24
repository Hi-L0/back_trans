<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Mission extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $dates = ['deleted_at'];
    protected $fillable = [                         //we did this just for now so we can fill the table
        'user_id',
        'client_id',
        'matricule',
        'nb_colis',
        'poids',
        'num_cmra',
        'num_declaration_transit',
        'destinataire',
        'commis',
        'photo_chargement',
        'bon_scaner',
        "num_mrn",
        'bl_maritime',
        'matricule_european',
        'photo_dechargement',
        'navire',
        'date_embarq',
        'etat',
    ];


    public function agent()
    {
        return $this->belongsTo('App\Models\Agent', 'user_id');  //one to many  //user has multiple missions but we dont know what user is this
    }
    public function isCommis()
    {
        return $this->belongsTo('App\Models\Agent', 'commis');
    }
    public function client()
    {
        return $this->belongsTo('App\Models\Client', 'client_id');
    }
    // public function getUpdatedAtAttribute($date)
    // {
    //     return Carbon::createFromFormat('Y-m-d H:i:s', $date)->format('Y-m-d H:i:s');
    // }
    public function facture()
    {
        return $this->hasOne('App\Models\Facture');
    }
}