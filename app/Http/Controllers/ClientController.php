<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\Facture;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;


class ClientController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        if (auth()->guard('api')->check()) {
            $this->middleware('auth:api', ['except' => ['login', 'register']]);
        }
        if (auth()->guard('agent-api')->check()) {
            $this->middleware('auth:agent-api', ['except' => ['login', 'register']]);
        }
    }
    public function index()
    {
        //$clients = Client::all();

        if (auth()->guard('api')->check()) {
            $clients = Client::wherehas('users', function ($query) {
                $query->where('user_id', auth()->guard('api')->user()->id);
                //$query->distinct()->whereIn('id', $request->users);
            })->orderBy('nom')->get();
        } else {
            // $agent=Agent::find(auth()->guard('agent-api')->user()->id);
            // $super = $agent->supervisor;
            $clients = Client::wherehas('users', function ($query) {
                $query->where('user_id', auth()->guard('agent-api')->user()->supervisor);
                //$query->distinct()->whereIn('id', $request->users);
            })->get();
        }
        return response()->json(
            [
                'status' => 'success',
                'clients' => $clients->toArray(),
            ]
        );
    }
    public function getAllClients()
    {
        $count = 0;
        if (auth()->guard('api')->check()) {
            $clients = Client::wherehas('users', function ($query) {
                $query->where('user_id', auth()->guard('api')->user()->id);
                //$query->distinct()->whereIn('id', $request->users);
            })->orderBy('nom')->get();
            $count = count($clients);
            return response()->json([
                'status' => 'success',
                'clientsCount' => $count,
                'clients' => $clients,
            ]);
        } else {
            return response()->json([
                'status' => 'failure',
                // 'clientsCount' => $count,
                'message' => 'Unauthorized'
            ]);
        }
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function myInvoices()
    {
        $count = 0;
        if (auth()->guard('client')->check()) {
            $factures = Facture::where([['client', auth()->guard('client')->user()->id], ['isClosed', true]])->get();
            $facCount = count($factures);
            return response()->json([
                'status' => 'success',
                'facturesCount' => $facCount,
                'factures' => $factures,
            ]);
        }
        return response()->json([
            'status' => 'failure',
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $val = Validator::make($request->all(), [
            "code" => 'required',
            "nom" => 'required',
            "prenom" => 'required',
            'societe' => 'required',
            'cri' => 'required',
            "email" => 'required',
            'ville' => 'required',
            'adresse' => 'required|string', //these column willbe added

        ]);

        if ($val->failed()) {
            return response()->json([
                'status' => false,
                'errors' => $val->errors()
            ], 400);
        }
        $password = bcrypt(strtolower($request->nom . $request->prenom)); //passwordformat

        $client = new Client();
        $client->code = strtoupper($request->code);
        $client->nom = $request->nom;
        $client->prenom = $request->prenom;
        $client->societe = $request->societe;
        $client->cri = $request->cri;
        $client->email =  $request->email;
        $client->ville = $request->ville;
        $client->tva = $request->tva;
        $client->adresse = $request->adresse;
        $client->cp = $request->cp;
        $client->pays = strtoupper($request->pays);
        $client->password = $password;
        $client->save();

        $client->users()->attach(auth()->guard('api')->user()->id);

        return response()->json(
            [
                'status' => 'success',
                'message' => 'client has been created successfully',
                'client' => $client,
            ]
        );
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show(Client $client)
    {
        if (auth()->guard('api')->check()) {
            $clients = Client::wherehas('users', function ($query) {
                $query->where('user_id', auth()->guard('api')->user()->id);
                //$query->distinct()->whereIn('id', $request->users);
            })->get();
            foreach ($clients as $item) {
                if ($item->id == $client->id) {
                    return response()->json([
                        'status' => 'success',
                        'client' => $client,
                    ]);
                }
            }
            if ($clients == null) {
                return response()->json([
                    'status' => 'success',
                    'message' => 'no client found',
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized',
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function edit(Client $client)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Client $client)
    {
        $val = Validator::make($request->all(), [
            'code' => 'string',
            'nom' => 'string',
            'prenom' => 'string',
            'societe' => 'string',
            'tva' => 'string',
            'cri' => 'string',
            'email' => 'string',
            'adresse' => 'string',
            'ville' => 'string',
            'cp' => 'integer',
            'pays' => 'string',
            //'password'=>'required|confirmed|min:6'      //i will see if i should implement it in the authController
        ]);
        if ($val->failed()) {
            return response()->json([
                'status' => 'failed',
                'message' => $val->errors(),
            ]);
        }
        if (auth()->guard('client')->id() == $client->id) {
            $client->nom = $request->nom;
            $client->prenom = $request->prenom;
            $client->email = $request->email;
            $client->adresse = $request->adresse;
            $client->ville = $request->ville;
            $client->cp = $request->cp;
            $client->pays = $request->pays;
            $client->societe = $request->societe;
            $client->cri = $request->cri;

            $client->save();
            return response()->json([
                'status' => 'success',
                'message' => 'your profile has been updated successfully',
                'client' => $client,
            ]);
        }
        if (auth()->guard('api')->check()) {
            $clients = Client::wherehas('users', function ($query) {
                $query->where('user_id', auth()->guard('api')->user()->id);
                //$query->distinct()->whereIn('id', $request->users);
            })->get();
            foreach ($clients as $item) {
                if ($item->id == $client->id) {
                    $client->code = $request->code;
                    $client->nom = $request->nom;
                    $client->prenom = $request->prenom;
                    $client->email = $request->email;
                    $client->adresse = $request->adresse;
                    $client->ville = $request->ville;
                    $client->cp = $request->cp;
                    $client->pays = $request->pays;
                    $client->societe = $request->societe;
                    $client->cri = $request->cri;
                    $client->tva = $request->tva;
                    $client->save();
                    return response()->json([
                        'status' => 'success',
                        'message' => 'your profile has been updated successfully',
                        'client' => $client,
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'action not authorized',
            ]);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function destroy(Client $client)
    {
        if (auth()->guard('api')->check()) {
            $client->delete();
            return response()->json([
                'status' => 'success',
                'message' => 'client deleted',
            ]);
        }
    }
}