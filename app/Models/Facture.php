<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Facture extends Model
{
    use HasFactory;
    protected $fillable = [
        'mission_id',
        'owner',
        'client',
        'code_facture',
        'designation',
        'description',
        'date',
        'unite',
        'quantite',
        'pu_ht',
        'pu_ttc',
        'remise',
        'total_ht',
        'total_ttc',
        'taxe',
        'net_payer_letters',
        'mode_reglement',
        'commantaire',
        'price_change',
        'taux_change',
        'delivery_note',
        'po_number',
        'invoiceNum',
        'isClosed',
    ];

    public function mission()
    {
        return $this->belongsTo('App\Models\Mission');
    }

    public function client()
    {
        return $this->belongsTo('App\Models\Client');
    }
}