<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ProfileController extends Controller
{
    //
    public function __construct()
    {
        if (auth()->guard('api')->check()) {
            $this->middleware("auth:api");
        }
        if (auth()->guard('agent-api')->check()) {
            $this->middleware("auth:agent-api");
        }
        if (auth()->guard('client')->check()) {
            $this->middleware("auth:client");
        }
    }

    public function myProfile()
    {
        $isAgent = true;
        if (auth()->guard('api')->check()) {
            $profile = User::where('id', auth()->guard('api')->user()->id)->get();
            $role = $profile[0]->roles;
        } elseif (auth()->guard('agent-api')->check()) {
            $profile = Agent::where('id', auth()->guard('agent-api')->id())->get();
            $role = $profile[0]->roles;
        } elseif (auth()->guard('client')->check()) {
            $profile = Client::where('id', auth()->guard('client')->id())->get();
            $isAgent = false;
        }

        return response()->json([
            'status' => 'success',
            'profile' => $profile,
            'isAgent' => $isAgent

        ]);
    }
    public function showThisProfile($id)
    {
        $isAgent = true;
        if (auth()->guard('api')->check()) {
            if (auth()->guard('api')->id() == $id) {
                $profile = User::where('id', $id)->get();
                $role = $profile[0]->roles;
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'unauthorized',
                ]);
            }
        } elseif (auth()->guard('agent-api')->check()) {
            if (auth()->guard('agent-api')->id() == $id) {
                $profile = Agent::where('id', $id)->get();
                $role = $profile[0]->roles;
            }
        } elseif (auth()->guard('client')->check()) {
            if (auth()->guard('client')->id() == $id) {
                $profile = Client::where('id', $id)->get();
                $isAgent = false;
            }
        }
        return response()->json([
            'status' => 'success',
            'profile' => $profile,
            'isAgent' => $isAgent

        ]);
    }
    public function updateMyProfile(Request $request, $id)
    {
        if (auth()->guard('api')->check()) {
            if (auth()->guard('api')->id() == $id) {

                $validate = Validator::make($request->all(), [
                    'name' => 'string',
                    'nom' => 'string',
                    'prenom' => 'string',
                    'adresse' => 'string',
                    'ville' => 'string',
                    'pays' => 'string',
                    'cp' => 'integer',
                    // 'societe' => 'string',   //if we were to update the admin's company we need to update it in all his agents
                    // 'update agents set societe=$request->societe where agents.supervisor=auth()->guard('api')->id();
                ]);
                if ($validate->failed()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $validate->errors(),
                    ]);
                }
                // $profile->name = $request->name;
                // $profile->nom = $request->nom;
                // $profile->prenom = $request->prenom;
                // $profile->adresse = $request->adresse;
                // $profile->ville = $request->ville;
                // $profile->pays = $request->pays;
                // $profile->cp = $request->cp;

                // $profile->save();
                User::whereId($id)->update([
                    'name' => $request->name,
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'adresse' => $request->adresse,
                    'ville' => $request->ville,
                    'gsm' => $request->gsm,
                    'pays' => strtoupper($request->pays),
                    'cp' => $request->cp,
                ]);
                $profile = User::where('id', $id)->get();
                return response()->json([
                    'status' => 'success',
                    'message' => 'profile has been updated',
                    'profile' => $profile,
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Unauthorized'
                ]);
            }
        } elseif (auth()->guard('agent-api')->check()) {
            if (auth()->guard('agent-api')->id() == $id) {

                $validate = Validator::make($request->all(), [
                    'name' => 'string',
                    'nom' => 'string',
                    'prenom' => 'string',
                    'adresse' => 'string',
                    'ville' => 'string',
                    'pays' => 'string',
                    'gsm' => 'integer',
                    'cp' => 'integer',
                ]);
                if ($validate->failed()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $validate->errors(),
                    ]);
                }
                Agent::whereId($id)->update([
                    'name' => $request->name,
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'adresse' => $request->adresse,
                    'ville' => $request->ville,
                    'pays' => $request->pays,
                    'cp' => $request->cp,
                    'gsm' => $request->gsm,
                ]);
                $profile = Agent::where('id', $id)->get();
                return response()->json([
                    'status' => 'success',
                    'message' => 'profile has been updated',
                    'profile' => $profile,
                ]);
            }
        } elseif (auth()->guard('client')->check()) {
            if (auth()->guard('client')->id() == $id) {

                $isAgent = false;
                $validate = Validator::make($request->all(), [
                    'nom' => 'string',
                    'prenom' => 'string',
                    'adresse' => 'string',
                    'ville' => 'string',
                    'pays' => 'string',
                    'cp' => 'integer',
                ]);
                if ($validate->failed()) {
                    return response()->json([
                        'status' => 'failed',
                        'message' => $validate->errors(),
                    ]);
                }
                Client::whereId($id)->update([
                    'nom' => $request->nom,
                    'prenom' => $request->prenom,
                    'adresse' => $request->adresse,
                    'ville' => $request->ville,
                    'pays' => $request->pays,
                    'cp' => $request->cp,
                ]);
                $profile = Client::where('id', $id)->get();
                return response()->json([
                    'status' => 'success',
                    'message' => 'profile has been updated',
                    'profile' => $profile,
                ]);
            }
        }
    }
}