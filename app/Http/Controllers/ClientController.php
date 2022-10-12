<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
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
            })->get();
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
            })->get();
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
        $password = bcrypt($request->nom . $request->prenom);

        $client = new Client();
        $client->code = $request->code;
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
                    'status' => 'failure',
                    'message' => 'Unauthorized',
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
        //
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