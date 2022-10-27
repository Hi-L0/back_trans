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
        $agents = Agent::where('supervisor', auth()->guard('api')->user()->id)->orderBy('name')->get();
        //$super = $agents->manager()->name;
        $agentsCount = count($agents);
        return response()->json([
            'status' => 'success',
            'myAgentsCount' => $agentsCount,
            'supervisor' => auth('api')->user()->name,
            'agents' => $agents,
        ]);
    }
    public function getAllTransporteur()
    {
        $transporteurs = Agent::where('supervisor', auth()->guard('api')->user()->id)->where('is_commis', false)->orderBy('name')->get();
        $transporteursCount = count($transporteurs);
        return response()->json([
            'status' => 'success',
            'mytransporteursCount' => $transporteursCount,
            'supervisor' => auth('api')->user()->name,
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
            $commis = Agent::where('supervisor', auth()->guard('api')->user()->id)->where('is_commis', true)->orderBy('name')->get();
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
            'name' => 'required|string',
            'email' => 'required|email',
            'gsm' => 'required|string',
            'adresse' => 'required|string',
            'ville' => 'required|string',
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validate->errors()
            ], 422);
        }
        if (strlen($request->name) >= 6) {
            $password = bcrypt(strtolower($request->name));
        } else {
            $password = bcrypt(strtolower($request->name . $request->name)); //forming short names into passwords :3
        }
        $is_Commis = 0;
        $supervisor = $user->id;
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
            'name' => $request->name,
            'email' => $request->email,
            'gsm' => $request->gsm,
            'adresse' => $request->adresse,
            'ville' => $request->ville,
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
        if ($agent->supervisor == auth('api')->user()->id) {
            $agentRole = $agent->roles()->select('code', 'desc')->get();
            //$supervisor = $agent->supervisor->name;  //we can't do supervisor cuz we have an existing column with the same name
            $supervisor = $agent->manager->name;
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
            'name' => 'string',
            'email' => 'string',
            'adresse' => 'string',
            'ville' => 'string',
        ]);

        if ($validate->failed()) {
            return response()->json([
                'status' => 'failed',
                'message' => $validate->errors(),
            ]);
        }
        if ((auth()->guard('api')->id() == $agent->supervisor) || (auth()->guard('agent-api')->id() == $agent->id)) {
            $agent->name = $request->name;
            $agent->email = $request->email;
            $agent->gsm = $request->gsm;
            $agent->adresse = $request->adresse;
            $agent->ville = $request->ville;
            $agent->password = bcrypt($request->password);
            $agent->save(); //not sure yet
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
            if ($agent->delete()) {

                return response()->json([
                    'status' => true,
                    'message' => "agent deleted successfully",
                    'agent' => $agent,
                ]);
            }
        } else {
            return response()->json([
                'status' => false,
                'message' => "Unauthorized",
            ]);
        }
    }
}