<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class AgentController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function __construct()
    {
        if (auth()->guard('api')->check()) {
            $this->middleware("auth:api");
        }
        if (auth()->guard('agent-api')->check()) {
            $this->middleware("auth:agent-api");
        };
    }

    public function index()
    {
        $fullName = auth()->guard('api')->user()->nom . ' ' . auth()->guard('api')->user()->prenom;
        $agents = Agent::where('supervisor', auth()->guard('api')->user()->id)->orderBy('nom')->get();
        //$super = $agents->manager()->name;
        $agentsCount = count($agents);
        return response()->json([
            'status' => 'success',
            'myAgentsCount' => $agentsCount,
            'supervisor' => $fullName,
            'agents' => $agents,
        ]);
    }
    public function getAllTransporteur()
    {
        $fullName = auth()->guard('api')->user()->nom . ' ' . auth()->guard('api')->user()->prenom;
        $transporteurs = Agent::where('supervisor', auth()->guard('api')->user()->id)->where('is_commis', false)->orderBy('nom')->get();
        $transporteursCount = count($transporteurs);
        return response()->json([
            'status' => 'success',
            'mytransporteursCount' => $transporteursCount,
            'supervisor' => $fullName,
            'transporteurs' => $transporteurs,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function getAllCommis()
    {
        if (auth()->guard('agent-api')->check()) {
            $agent = auth()->guard('agent-api')->user();
            $commis = Agent::where('supervisor', $agent->supervisor)->where('is_commis', true)->get();
        } elseif (auth()->guard('api')->check()) {
            $commis = Agent::where('supervisor', auth()->guard('api')->user()->id)->where('is_commis', true)->orderBy('nom')->get();
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ]);
        }
        $agentsCount = count($commis);
        // for ($i = 1; $i <= $agentsCount; $i++) {
        //     if ($agents[0]->id == $i) {
        //         $agent = Agent::find($i);
        //         $commis = $agent->roles()->where('code', 'co')->get();
        //     }
        // }
        // $agent = $agents;


        return response()->json([
            'commis_count' => $agentsCount,
            'commis' => $commis,
            //'agents' => $agents,

        ]);
    }
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
        $user = auth()->guard('api')->user();
        $validate = Validator::make($request->all(), [
            // 'name' => 'required|string',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'email' => 'required|email',
            'gsm' => 'required|string',
            'adresse' => 'required|string',
            'ville' => 'required|string',
            'cp' => 'integer',
            'pays' => 'string',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validate->errors()
            ], 422);
        }
        $username = strtolower($request->nom . $request->prenom);

        if (strlen($username) >= 6) {
            $password = bcrypt($username);
        } else {
            $password = bcrypt($username); //forming short names into passwords :3
        }
        $is_Commis = 0;
        $supervisor = $user->id;
        $societe = $user->societe;  //societe column is defined by the suppervisor's company
        if (is_array($request->roles)) {
            foreach ($request->roles as $i => $value) {
                if ($request->roles[$i] == 3) {      //3 means Role::find(3)->id;
                    $is_Commis = 1;
                }
            }
        }
        if ($request->roles == 3) {      //3 means Role::find(3)->id;
            $is_Commis = 1;
        }

        // $id = User::find(1);
        $agent = Agent::create([
            'supervisor' => $supervisor,
            'name' => $username,
            'nom' => $request->nom,
            'prenom' => $request->prenom,
            'email' => $request->email,
            'societe' => $societe,
            'gsm' => $request->gsm,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
            'cp' => $request->cp,
            'pays' => $request->pays,
            'is_commis' => $is_Commis,
            'password' => $password,

        ]);

        $agent->roles()->attach($request->roles); //we should add roles input in the frontend
        // $agent->supervisor()->attach($user->id); //column superviseur add admin

        return response()->json([
            'status' => 'success',
            'message' => 'agent created successfully',
            'agent' => $agent,
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function show(Agent $agent)
    {
        if (($agent->supervisor == auth('api')->user()->id)) {
            $agentRole = $agent->roles()->select('code', 'desc')->get();
            //$supervisor = $agent->supervisor->name;  //we can't do supervisor cuz we have an existing column with the same name
            $supervisor = $agent->manager->nom . ' ' . $agent->manager->prenom;

            return response()->json([
                'status' => 'success',
                'supervisor' => $supervisor,
                'role' => $agentRole,
                'agent' => $agent,

            ]);
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
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function edit(Agent $agent)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Agent $agent)
    {
        $validate = Validator::make($request->all(), [

            'email' => 'string',
            'gsm' => 'string',
            'adresse' => 'string',
            'ville' => 'string',
            'pays' => 'string',
            'cp' => 'string',
            'societe' => 'string'
            // 'password' => 'required|confirmed|min:6',   //we should implement this in the authcontrollers
            // 'password_confirmation' => 'required'
        ]);

        if ($validate->failed()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validate->errors(),
            ]);
        }
        if ((auth()->guard('api')->id() == $agent->supervisor) || (auth()->guard('agent-api')->id() == $agent->id)) {
            $agent->societe = $request->societe;
            $agent->name = $request->name;
            $agent->nom = $request->nom;
            $agent->prenom = $request->prenom;
            $agent->email = $request->email;
            $agent->gsm = $request->gsm;
            $agent->adresse = $request->adresse;
            $agent->ville = $request->ville;
            $agent->pays = strtoupper($request->pays);
            $agent->cp = $request->cp;
            // if (strlen($request->password) >= 6) {
            //     $agent->password = bcrypt($request->password);
            //     $agent->save();
            // } else {
            //     return response()->json([
            //         "message" => "password too short",
            //     ]);
            // }
            //not sure yet
            $agent->save();
            return response()->json([
                'status' => 'success',
                'message' => 'profile has been updated successfully',
                'agent' => $agent,
            ]);
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
     * @param  \App\Models\Agent  $agent
     * @return \Illuminate\Http\Response
     */
    public function destroy(Agent $agent)
    {
        if (auth()->guard('api')->check()) {
            if (auth()->guard('api')->id() == $agent->supervisor) {
                if ($agent->delete()) {

                    return response()->json([
                        'status' => true,
                        'message' => "agent deleted successfully",
                        'agent' => $agent,
                    ]);
                }
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Unauthorized",
            ]);
        }
    }
}