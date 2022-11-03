<?php

namespace App\Http\Controllers\admin;

use App\Http\Controllers\Controller;
use App\Models\Facture;
use App\Models\Mission;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Facades\Validator;

class FactureController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }
    public function getFactures()
    {
        $factures = Facture::where('owner', auth()->guard('api')->id())->get();
        return response()->json([
            'status' => 'success',
            'factures' => $factures,
        ]);
    }
    public function createFacture(Request $req, Mission $mission)
    {
        //return $mission->id;
        if (auth()->guard('api')->check()) {
            if ($mission->etat == 4) {
                $validator = Validator::make($req->all(), [
                    'code_facture' => 'required',
                    'designation' => 'required',
                    'description' => 'required',
                    'unite' => 'integer',
                    'quantite' => 'required',
                    'pu_ht' => 'required',
                    'pu_ttc' => 'required',
                    'remise' => 'float',
                    'total_ht' => 'required',
                    'total_ttc' => 'required',
                    'taxe' => 'string|required',
                    'net_payer_letters' => 'required|string',
                    'mode_reglement' => 'required',
                    'commantaire' => 'string',
                    'price_change' => 'flaot',
                    'taux_change' => 'float',
                    'delivery_note' => 'required',
                    'po_number' => 'required',
                    'invoiceNum' => 'required',

                ]);

                if ($validator->failed()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $validator->errors(),
                    ], 400);
                }
                $client = $mission->client;
                //$transporteur = $mission->agent;

                $facture = Facture::create(
                    [
                        'mission_id' => $mission->id,
                        'owner' => auth()->guard('api')->user()->id,
                        'client' => $client->id,
                        'code_facture' => $req->code_facture,
                        'designation' => $req->designation,
                        'description' => $req->description,
                        'date' => date('Y-m-d'),
                        'unite' => $req->unite,
                        'quantite' => $req->quantite,
                        'pu_ht' => $req->pu_ht,
                        'pu_ttc' => $req->pu_ttc,
                        'remise' => $req->remise,
                        'total_ht' => $req->total_ht,
                        'total_ttc' => $req->total_ttc,
                        'taxe' => $req->taxe,
                        'price_change' => $req->price_change,
                        'taux_change' => $req->taux_change,
                        'delivery_note' => $req->delivery_note,
                        'po_number' => $req->po_number,
                        'invoiceNum' => $req->invoiceNum,
                        'net_payer_letters' => $req->net_payer_letters,
                        'mode_reglement' => $req->mode_reglement,
                        'commantaire' => $req->commantaire,

                    ]
                );
                return response()->json([
                    'status' => 'success',
                    'message' => 'facture created',
                    'facture' => $facture,
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => "can't create facture to an unfinished mission",
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized',

            ]);
        }
    }
    public function show(Mission $mission)
    {
        $facs = Facture::where('mission_id', $mission->id)->get();
        $facture = $mission->facture;
        if (auth()->guard('api')->id() == $facture->owner)
        // $mission = $facture->mission;
        {
            return response()->json([
                'status' => 'success',
                'mission' => $mission,
                //'factures' => $facs,
                //'facture' => $facture,
            ]);
        } else {
            return response()->json([
                'message' => 'Unauthorized',
            ]);
        }
    }
    public function closeFacture(Mission $mission)
    {
        if (auth()->guard('api')->check()) {
            $facture = $mission->facture;
            $facture->isClosed = true;
            $facture->save();

            return response()->json([
                'status' => 'success',
                'message' => 'facture is closed',
            ]);
        }
    }

    public function getClosedFactures()
    {
        $count = 0;
        if (auth()->guard('api')->check()) {
            $facs = auth()->guard('api')->user()->closedFactures;
            $count = count($facs);
            return response()->json([
                'status' => 'success',
                'count' => $count,
                'factures' => $facs,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ]);
        }
    }

    public function updateFacture(Request $request, Mission $mission)
    {
        $facture = $mission->facture;
        $validator = Validator::make($request->all(), [
            'code_facture' => 'required',
            'designation' => 'required',
            'description' => 'required',
            'date' => 'date',
            'unite' => 'integer',
            'quantite' => 'required',
            'pu_ht' => 'required',
            'pu_ttc' => 'required',
            'remise' => 'float',
            'total_ht' => 'required',
            'total_ttc' => 'required',
            'taxe' => 'string|required',
            'net_payer_letters' => 'required|string',
            'mode_reglement' => 'required',
            'commantaire' => 'string',
            'price_change' => 'float',
            'taux_change' => 'float',
            'delivery_note' => 'required',
            'po_number' => 'required',
            'invoiceNum' => 'required',
        ]);
        if ($validator->failed()) {
            return response()->json([
                'status' => false,
                'errors' => $validator->errors()
            ], 400);
        }
        if ($facture->isClosed == 0) {  //or false
            $facture->code_facture = $request->code_facture;
            $facture->designation = $request->designation;
            $facture->description = $request->description;
            $facture->date = $request->date;
            $facture->unite = $request->unite;
            $facture->quantite = $request->quantite;
            $facture->pu_ht = $request->pu_ht;
            $facture->pu_ttc = $request->pu_ttc;
            $facture->remise = $request->remise;
            $facture->total_ht = $request->total_ht;
            $facture->total_ttc = $request->total_ttc;
            $facture->taxe = $request->taxe;
            $facture->net_payer_letters = $request->net_payer_letters;
            $facture->mode_reglement = $request->mode_reglement;
            $facture->commantaire = $request->commantaire;
            $facture->price_change = $request->price_change;
            $facture->taux_change = $request->taux_change;
            $facture->delivery_note = $request->delivery_note;
            $facture->po_number = $request->po_number;
            $facture->invoiceNum = $request->invoiceNum;

            $facture->save();

            return response()->json([
                'status' => 'success',
                'message' => 'facture updated successfully',
            ]);
        } else {
            return response()->json([
                'message' => "this facture is closed, you can't modify it",
            ]);
        }
    }

    public function showFacInfo(Mission $mission)
    {
        $agent = $mission->agent;
        if (auth()->guard('api')->check()) {
            if (auth()->guard('api')->user()->id == $agent->supervisor) {
                $missionData = $mission;
                $client = $mission->client->nom . ' ' . $mission->client->prenom;
                $factureData = $mission->facture;
                $transporteur_fullname = $mission->agent->nom . ' ' . $mission->agent->prenom;
                $commis = $mission->isCommis->nom . ' ' . $mission->isCommis->prenom;

                return response()->json([
                    'transporteur' => $transporteur_fullname,
                    'commis' => $commis,
                    'mission_info' => $missionData,
                    'client' => $client,
                    'facture_Info' => $factureData,
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Unauthorized'
                ]);
            }
        }
    }


    public function generateFacture(Mission $mission)
    {
        if ($mission->etat == 4) {
            $facture = $mission->facture;

            if (auth()->guard('api')->user()->id == $facture->owner) {
                $facture = $mission->facture;
                $client = $mission->client;  //so that the attribut 'client' show up in the json call
                $myFacture = 'factures/' . $facture->code_facture . '.pdf';
                $data = [
                    'mission' => $mission,
                    // 'date' => date('d-m-Y'),
                ];
                //PDF::loadHTML($content)->save($myFacture);
                PDF::loadView('content', $data)->save($myFacture);
                $model = Facture::find($facture->id);
                $model->facture = $myFacture;
                $model->save();
                $mission->invoice = true;
                $mission->save();
                return response()->download($myFacture);
            } else {
                return response()->json([
                    'message' => 'Unauthorized',
                ]);
            }
        } else {
            return response()->json([
                "message" => 'no facture found',
            ]);
        }
    }
    public function destroy(Facture $facture)
    {
        if (auth()->guard('api')->user()->id == $facture->owner) {
            $mission = Mission::where('id', $facture->mission_id)->get();
            $facture->delete();
            $mission->invoice = false;
            $mission->save();
            return response()->json([
                'status' => 'success',
                'message' => 'facture has been deleted successfully',
            ]);
        }
    }
}