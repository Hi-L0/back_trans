<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\Facture;
use App\Models\Mission;
use App\Models\User;
use Hamcrest\Core\HasToString;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Symfony\Component\Console\Input\Input;
use Illuminate\Support\Facades\File;

use function PHPUnit\Framework\isNull;

class MissionController extends Controller
{

    public function __construct()
    {
        // $this->user = auth()->guard('api')->user();
        // $this->agent = auth()->guard('agent-api');
        if (auth()->guard('api')->check()) {
            $this->middleware("auth:api");
            $this->user = auth()->guard('api')->user();
        } elseif (auth()->guard('agent-api')->check()) {
            $this->middleware("auth:agent-api");
            $this->user = auth()->guard('agent-api')->user();
        } elseif (auth()->guard('client')->check()) {
            $this->user = auth()->guard('client')->user();
        }
    }

    /**
     * Display a listing of the resource.
     * @param int represents the year
     * @return \Illuminate\Http\Response
     */

    public function missionsPerMonth($an)
    {
        if (auth()->guard('api')->check()) {
            //this admins missions
            $missions = auth()->guard('api')->user()->missions;
            $count = 0;
            //these missions ids
            $myMissionsIds = [];
            $counter = 0;
            foreach ($missions as $item) {
                $i = $counter;
                $myMissionsIds[$i] = $item->id;
                $counter = $i + 1;
            }
            $data = Mission::select(
                DB::raw('count(*) as missionsCount'),
                DB::raw("DATE_FORMAT(created_at,'%M %Y') as months"),
                DB::raw("DATE_FORMAT(created_at,'%Y') as year"),
                DB::raw("DATE_FORMAT(created_at,'%m') as monthKey")
            )
                ->whereIn('id', $myMissionsIds)
                //->where('etat', 4) //for the completed missions
                ->whereYear('created_at', '=', $an)
                ->groupBy('months', 'year', 'monthKey')->get();
            $missionsPerMonth = [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0];
            foreach ($data as $item) {
                $missionsPerMonth[$item->monthKey - 1] = $item->missionsCount;
                $count = $count + $item->missionsCount;
            }
            return response()->json([
                'status' => 'success',
                'count' => $count,
                'data' => $data,
                'missionsPermonth' => $missionsPerMonth,
                'myMissions' => $myMissionsIds,
            ]);
        }

        return response()->json([
            'status' => 'danger',
            'message' => 'Unauthorized'
        ]);
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        if (auth()->guard('api')->check()) {
            $user = auth()->guard('api')->user();
        } elseif (auth()->guard('agent-api')->check()) {
            $user = auth()->guard('agent-api')->user();
        }
        // $agent = Auth::guard('agent');
        $nbre = 0;
        if (auth()->guard('client')->check()) {
            $user = auth()->guard('client')->user();
            $missions = auth()->guard('client')->user()->missions;
            $nbre = count($missions);
            // $nom = $user->nom;
            // $prenom = $user->prenom;
            return response()->json(
                [
                    'status' => 'success',
                    'user' => $this->user->nom . ' ' . $this->user->prenom,
                    'missionsCount' => $nbre,
                    "missions" => $missions,
                ]
            );
        } else {

            $role = $this->user->roles()->select('code')->get();

            for ($i = 0; $i < sizeof($role); $i++) {
                if ($role[$i]->code == 'admin') {
                    // $missions = Mission::latest()->get(); we have to get the missions that belongs to this specific admin
                    // $nbre = count($missions);
                    $missions = auth()->guard('api')->user()->missions;
                    $nbre = count($missions);
                } elseif ($role[$i]->code == 'tr') {
                    $missions = Mission::where('user_id', auth()->guard('agent-api')->user()->id)->latest()->get();
                    $nbre = count($missions);
                } else {
                    $missions = Mission::where('commis', $user->id)->latest()->get();
                    $nbre = count($missions);
                }
            }
            // if ($role == 'admin') {
            //     $missions = Mission::all()->latest();
            // } else {
            //     $missions = Mission::where('user_id', $user->id)->latest()->get();
            //     $nbre = count($missions);
            // }
        }
        return response()->json(
            [
                'status' => 'success',
                'user' => $this->user->name,
                'role' => $role,
                'missionsCount' => $nbre,
                "missions" => $missions,
            ]
        );
    }
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function missionsInProg()
    {
        if (auth()->guard('api')->check()) {
            $user = auth()->guard('api')->user();
        } elseif (auth()->guard('agent-api')->check()) {
            $user = auth()->guard('agent-api')->user();
        }
        // $agent = Auth::guard('agent');
        $nbre = 0;
        if (auth()->guard('client')->check()) {
            $user = auth()->guard('client')->user();
            $missions = Mission::where([['client_id', auth()->guard('client')->user()->id], ['etat', "!=", 4]])->orderBy('created_at', 'DESC')->get();

            $nbre = count($missions);

            // $nom = $user->nom;
            // $prenom = $user->prenom;
            return response()->json(
                [
                    'status' => 'success',
                    'user' => $this->user->nom . ' ' . $this->user->prenom,
                    'missionsCount' => $nbre,
                    "missions" => $missions,
                ]
            );
        } else {

            $role = $this->user->roles()->select('code')->get();

            for ($i = 0; $i < sizeof($role); $i++) {
                if ($role[$i]->code == 'admin') {
                    // $missions = Mission::latest()->get(); we have to get the missions that belongs to this specific admin
                    // $nbre = count($missions);
                    $missions = auth()->guard('api')->user()->missions_inprogress;
                    $nbre = count($missions);
                } elseif ($role[$i]->code == 'tr') {
                    $missions = Mission::where([['user_id', auth()->guard('agent-api')->user()->id], ['etat', '!=', 4]])->latest()->get();
                    $nbre = count($missions);
                } else {
                    $missions = Mission::where([['commis', $user->id], ['etat', '!=', 4]])->latest()->get();
                    $nbre = count($missions);
                }
            }
            // if ($role == 'admin') {
            //     $missions = Mission::all()->latest();
            // } else {
            //     $missions = Mission::where('user_id', $user->id)->latest()->get();
            //     $nbre = count($missions);
            // }
        }
        return response()->json(
            [
                'status' => 'success',
                'user' => $this->user->nom . ' ' . $this->user->prenom,
                'role' => $role,
                'missionsCount' => $nbre,
                "missions" => $missions,
            ]
        );
    }

    /**
     * getFinishedMission display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function getFinishedMission()
    {
        //
        //$mission = Mission::all();
        $count = 0;
        // $mission = array();
        if (auth()->guard('client')->check()) {
            $missions = Mission::where([['client_id', auth()->guard('client')->id()], ['etat', 4]])->latest()->get();
            $count = count($missions);
            return response()->json([
                'status' => 'success',
                'missionsCount' => $count,
                'missions_completed' => $missions,
            ]);
        }

        //----this is admin

        if (auth()->guard('api')->check()) {
            $missions = auth()->guard('api')->user()->finishedMissions;
            //$missions = Mission::where('etat', 4)->orderBy('updated_at', 'DESC')->get();
            $count = count($missions);
            return response()->json([
                'status' => 'success',
                'missionsCount' => $count,
                'missions_completed' => $missions,
            ]);
        }
        //----this for agents

        if (auth()->guard('agent-api')->check()) {
            //$user = auth()->guard('agent-api')->user();
            $role = $this->user->roles()->select('code')->get();
            for ($i = 0; $i < sizeof($role); $i++) { //---missions for transporteur
                if ($role[$i]->code == 'tr') {
                    $missions = Mission::where([['user_id', auth()->guard('agent-api')->id()], ['etat', 4]])->latest()->get();
                    $count = count($missions);
                } else { //this is commis missions :)
                    $missions = Mission::where([['commis', auth()->guard('agent-api')->id()], ['etat', 4]])->latest()->get();
                    $count = count($missions);
                }
            }
            return response()->json([
                'status' => 'success',
                'missionsCount' => $count,
                'missions_completed' => $missions,
            ]);
        } else {
            return response()->json([
                'status' => 'failed',
                'message' => 'Unauthorized',
            ]);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //if admin is connected
        if (auth()->guard('api')->check()) {
            $user = $this->user; //auth()->guard('api')->user(); or auth()->guard('agent-api')->user();
            $val = Validator::make($request->all(), [
                'user_id' => 'required|integer',
                'client_id' => 'required|integer',
                'matricule' => 'required|string',
                'nb_colis' => 'required',
                'poids' => 'required',
                'num_cmra' => 'required',
                'num_declaration_transit' => 'required',
                'destinataire' => 'required|string',
                'commis' => 'required',
                'photo_chargement' => 'image',
                'bon_scaner' => 'string',
                'num_mrn' => 'string',
                'bl_maritime' => 'string',
                'matricule_european' => 'string',

            ]);

            $ref_photo = $request->matricule;
            if ($request->hasFile('photo_chargement')) {
                $pic = $request->file('photo_chargement');
                $photoName = $ref_photo . '_' . date('y_m_d') . '.' . $pic->getClientOriginalExtension();
                $pic->move('uploads/missioncharge/', $photoName);
            }
            if ($val->failed()) {
                return response()->json([
                    'status' => false,
                    'errors' => $val->errors()
                ], 400);
            }
            $mission = Mission::create([
                'user_id' => $request->user_id,
                'client_id' => $request->client_id,
                'matricule' => $request->matricule,
                'nb_colis' => $request->nb_colis,
                'poids' => $request->poids,
                'num_cmra' => $request->num_cmra,
                'num_declaration_transit' => $request->num_declaration_transit,
                'destinataire' => $request->destinataire,
                'commis' => $request->commis,
                'photo_chargement' => 'uploads/missioncharge/' . $photoName,
            ]);
            //$mission->save();


            return response()->json([
                'status' => true,
                'message' => 'mission created!',
                'mission' => $mission,
                // 'step' => "initialisation", //as a value and not in the data base
            ]);
        } elseif (auth()->guard('agent-api')->check()) { //if transporteur is connected
            $role = $this->user->roles()->select('code')->get();
            //condition for only agents that are transporters can create new missions
            if ($role[0]->code == 'tr') {
                $user = $this->user;
                $val = Validator::make($request->all(), [
                    'client_id' => 'required|integer',
                    'matricule' => 'required|string',
                    'nb_colis' => 'required',
                    'poids' => 'required',
                    'num_cmra' => 'required',
                    'num_declaration_transit' => 'required',
                    'destinataire' => 'required|string',
                    'commis' => 'required',
                    'photo_chargement' => 'image',
                    'bon_scaner' => 'string',
                    'num_mrn' => 'string',
                    'bl_maritime' => 'string',
                    'matricule_european' => 'string',

                ]);

                $ref_photo = $request->matricule;
                if ($request->hasFile('photo_chargement')) {
                    $pic = $request->file('photo_chargement');
                    $photoName = $ref_photo . '_' . date('y_m_d') . '.' . $pic->getClientOriginalExtension();
                    $pic->move('uploads/missioncharge/', $photoName);
                }
                // if ($request->has('photo_chargement')) {
                //     $image = $request->file('photo_chargement');
                //     //foreach ($request->file('photo_chargement') as $image)
                //     $filename = time() . $ref_photo . '.' . $image->getClientOriginalExtension();
                //     $image->move('uploads/', $filename);
                //     $mission = Mission::create([
                //         'user_id' => $user->id,
                //         'client_id' => $request->client_id,
                //         'matricule' => $request->matricule,
                //         'nb_colis' => $request->nb_colis,
                //         'poids' => $request->poids,
                //         'num_cmra' => $request->num_cmra,
                //         'num_declaration_transit' => $request->num_declaration_transit,
                //         'destinataire' => $request->destination,
                //         'commis' => $request->commis,
                //         'photo_chargement' => 'uploads/' . $filename,

                //     ]);
                // }

                if ($val->failed()) {
                    return response()->json([
                        'status' => false,
                        'errors' => $val->errors()
                    ], 400);
                }
                // if (auth()->guard('api')->check()) {
                //     $user = auth()->guard('api')->user();
                // } elseif (auth()->guard('agent-api')->check()) {
                //     $user = auth()->guard('agent-api')->user();
                // }

                //$user = User::find(1); //look above
                $mission = Mission::create([
                    'user_id' => $user->id,
                    'client_id' => $request->client_id,
                    'matricule' => $request->matricule,
                    'nb_colis' => $request->nb_colis,
                    'poids' => $request->poids,
                    'num_cmra' => $request->num_cmra,
                    'num_declaration_transit' => $request->num_declaration_transit,
                    'destinataire' => $request->destinataire,
                    'commis' => $request->commis,
                    'photo_chargement' => 'uploads/missioncharge/' . $photoName,
                ]);
                //$mission->save();


                return response()->json([
                    'status' => true,
                    'message' => 'mission created!',
                    'mission' => $mission,
                    // 'step' => "initialisation", //as a value and not in the data base
                ]);
            } else {
                return response()->json([
                    'status' => 'danger',
                    'message' => 'this user cannot do this action ',
                ]);
            }
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Mission  $mission
     * @return \Illuminate\Http\Response
     */
    public function show(Mission $mission)
    {
        // $missions = Mission::where('id', $mission->id)->get();
        // return ([
        //     'status' => 'success',
        //     'missions' => $missions,

        // ]);

        // $user = Auth::user(); //Auth::user
        $agent = $mission->agent;
        $fullname = $mission->agent->nom . ' ' . $mission->agent->prenom; //agent's full name
        $commis = $mission->isCommis->nom . ' ' . $mission->isCommis->prenom; //commis' full name
        //we either change this from separate client proprieteies to a client object
        $clientName = $mission->client->nom . ' ' . $mission->client->prenom;
        // $clientPren = $mission->client->prenom;
        $clientCompany = $mission->client->societe;
        //client as object
        //$clientInfo = $mission->client; //all we need is to implement this in api calls
        if (auth()->guard('agent-api')->check()) {
            $role = $this->user->roles()->select('code')->get();
            for ($i = 0; $i < sizeof($role); $i++) {
                if ($role[$i]->code == 'co') {
                    if (auth()->guard('agent-api')->user()->id == $mission->commis) {
                        $role = $this->user->roles()->select('code')->get();
                        return response()->json([
                            'status' => 'success',
                            'transporteur' => $fullname,
                            'role' => $role,
                            'commis' => $commis,
                            'client' => $clientName,
                            'clientCompany' => $clientCompany,
                            'mission' => $mission,
                            'step' => $mission->etat,
                        ]);
                    } else {
                        return response()->json([
                            'status' => 'failure',
                            'message' => 'Unauthorized',
                        ]);
                    }
                } elseif ($role[$i]->code == 'tr') {
                    if (auth()->guard('agent-api')->user()->id == $mission->user_id) {
                        $role = $this->user->roles()->select('code')->get();
                        return response()->json([
                            'status' => 'success',
                            'transporteur' => $fullname,
                            'role' => $role,
                            'commis' => $commis,
                            'client' => $clientName,
                            'clientCompany' => $clientCompany,
                            'mission' => $mission,

                        ]);
                    } else {
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
        } elseif (auth()->guard('api')->check()) {
            if (auth()->guard('api')->user()->id == $agent->supervisor) {
                $role = $this->user->roles()->select('code')->get();
                // $name = $mission->agent->name;
                // $commis = $mission->isCommis->name;
                // $clientName = $mission->client->nom;
                // $clientPren = $mission->client->prenom;

                return response()->json([
                    'status' => 'success',
                    'transporteur' => $fullname,
                    'role' => $role,
                    'commis' => $commis,
                    'client' => $clientName,
                    'clientCompany' => $clientCompany,
                    'mission' => $mission,
                    'step' => $mission->etat,
                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Unauthorized'
                ]);
            }
        } elseif (auth()->guard('client')->check()) {
            if (auth()->guard('client')->id() == $mission->client_id) {
                $client = auth()->guard('client')->user();
                // $trans = $mission->agent->name;
                // $commis = $mission->isCommis->name;
                return response()->json([
                    'status' => 'success',
                    'transporteur' => $fullname,
                    'commis' => $commis,
                    'client' => $client->nom . ' ' . $client->prenom,
                    'clientCompany' => $client->societe,
                    'mission' => $mission,
                    'viewSteps' => 'yes',

                ]);
            } else {
                return response()->json([
                    'status' => 'failure',
                    'message' => 'Unauthorized'
                ]);
            }
        } else {
            return response()->json([
                'status' => 'failure',
                'message' => 'Unauthorized'
            ]);
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Models\Mission  $mission
     * @return \Illuminate\Http\Response
     */
    public function edit(Mission $mission)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Mission  $mission
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Mission $mission)
    {
        $val = Validator::make($request->all(), [
            'matricule' => 'string',
            'nb_colis' => 'string',
            'poids' => 'float',
            'num_cmra' => 'string',
            'num_declaration_transit' => 'string',
            'destinataire' => 'string',
            // 'commis' => 'required',
            // 'photo_chargement' => 'required|image',
            'bon_scaner' => 'required|string',
            'num_mrn' => 'required|string',
            'bl_maritime' => "string",
            'matricule_european' => "string",
            'photo_dechargement' => "image",
            'navire' => 'string',
            'date_embarq' => 'string',
            'etat' => "integer",
        ]);

        if ($val->failed()) {
            return response()->json([
                'status' => false,
                'errors' => $val->errors()
            ], 400);
        }
        if ($mission->isModifiable == true) {
            $mission->matricule = $request->matricule;
            $mission->nb_colis = $request->nb_colis;
            $mission->poids = $request->poids;
            $mission->num_cmra = $request->num_cmra;
            $mission->num_declaration_transit = $request->num_declaration_transit;
            $mission->destinataire = $request->destinataire;
            $mission->bon_scaner = $request->bon_scaner;
            $mission->num_mrn = $request->num_mrn;
            $mission->navire = $request->navire;
            $mission->bl_maritime = $request->bl_maritime;
            $mission->date_embarq = $request->date_embarq;
            $mission->matricule_european = $request->matricule_european;
            $ref_photo = $mission->matricule;
            if ($request->hasFile('photo_dechargement')) {
                $pic = $request->file('photo_dechargement');
                $photoName = $ref_photo . '_' . strftime('%y_%m_%d', strtotime($mission->created_at)) . '.' . $pic->getClientOriginalExtension();
                $pic->move('uploads/missiondecharge/', $photoName);
                $mission->photo_dechargement = 'uploads/missiondecharge/' . $photoName;
            }
            //the user will dectate which state the mission's at
            $mission->etat = $request->etat;


            //$steps = [1, 2, 3, 4];  //steps for the missions

            // if (!is_null($mission->bon_scaner) && !is_null($mission->num_mrn) && !is_null($mission->navire)) {     //so that we can track mission's steps
            //     $mission->etat = $steps[1];
            //     if (!is_null($mission->bl_maritime) && !is_null($mission->date_embarq)) {
            //         $mission->etat = $steps[2];
            //         if (!is_null($mission->matricule_european) && !is_null($mission->photo_dechargement)) {
            //             $mission->etat = $steps[3];
            //         }
            //     }
            // } else {
            //     $mission->etat = $steps[0];
            // }
            $mission->save();
            return response()->json([
                'status' => 'success',
                'message' => 'mission updated successfully',
                'step' => $mission->etat,
                'mission' => $mission,

            ]);
        } else {
            return response()->json([
                'status' => 'danger',
                'message' => 'you cannot modify this mission',
            ]);
        }
        //we wont need this
        if ($mission->etat != 4 && $mission->invoice == true) {
            $thisFac = $mission->facture;
            $thisFac->delete();
            $mission->invoice = false;
            $mission->isModifiable = true;
            $mission->save();
            return response()->json([
                'status' => 'warning',
                'message' => '',
                'mission' => $mission,

            ]);
        }
    }
    //this function is for sending photo_dechargement (file) (update function)
    public function FileSending(Request $request, Mission $mission)
    {
        if (auth()->guard('api')->check() || auth()->guard('agent-api')->check()) {
            if ($mission->isModifiable == true) {
                $ref_photo = $mission->matricule;
                if ($request->hasFile('photo_chargement')) {
                    $pic = $request->file('photo_chargement');
                    $photoName = $ref_photo . '_' . strftime('%y_%m_%d', strtotime($mission->created_at)) . '.' . $pic->getClientOriginalExtension();
                    $pic->move('uploads/missioncharge/', $photoName);
                    $mission->photo_chargement = 'uploads/missioncharge/' . $photoName;
                }
                if ($request->hasFile('photo_dechargement')) {
                    $pic = $request->file('photo_dechargement');
                    $photoName = $ref_photo . '_' . strftime('%y_%m_%d', strtotime($mission->created_at)) . '.' . $pic->getClientOriginalExtension();
                    $pic->move('uploads/missiondecharge/', $photoName);
                    $mission->photo_dechargement = 'uploads/missiondecharge/' . $photoName;
                    //$mission->etat = 4; we won't need this after we removed the condition on etat column
                }
                $mission->save();
                return response()->json([
                    'status' => 'success',
                    'message' => 'mission updated successfully',
                    'step' => $mission->etat,
                    'mission' => $mission,

                ]);
            }
        } else {
            return response()->json([
                'status' => 'danger',
                'message' => 'Unauthorized',
            ]);
        }
    }


    public function missionTrashed()
    {
        if (auth()->guard('api')->check()) {
            $count = 0;
            $user = auth()->guard('api')->user(); //Auth::user
            // $missions = Mission::onlyTrashed()->where('user_id', $user->id)->get();  //it shows only the post of the user
            //or we can write with(.....)

            //$missions = Mission::onlyTrashed()->where('user_id', Auth::id())->get();  //it shows only the post of the user
            //or we can write with(.....)
            $missions = $user->trashedMissions;
            $count = count($missions);
            return response()->json([
                'status' => 'success',
                'missionsCount' => $count,
                'missions_deleted' => $missions,
            ]);
        }
        return response()->json([
            'status' => 'danger',
            'message' => 'Unauthorized',
        ]);
    }

    /**
     * Soft delete the specified resource from storage.
     *
     * @param  \App\Models\Mission  $mission
     * @return \Illuminate\Http\Response
     */
    public function destroy(Mission $mission)
    {

        /* if we want to delete mission images */
        // if (File::exists(public_path($mission->photo_chargement))) {
        //     File::delete(public_path($mission->photo_chargement));
        // } //actually we wont delete mission files cuz we have the soft delete enabled in missions table
        if ($mission->delete()) {
            return response()->json([
                'status' => 'success',
                'message' => "mission deleted successfully",
                //'mission' => $mission,
            ]);
        } else {
            return response()->json([
                'status' => 'danger',
                'message' => "the mission could not be deleted",
            ]);
        }
    }
    /**
     * Restore the specified resource from storage.
     *
     * @param  \App\Models\Mission  $mission
     * @return \Illuminate\Http\Response
     */
    public function restoreMission($id)
    {
        $mission = Mission::withTrashed()->where('id', $id)->first();
        if ($mission->restore()) {
            return response()->json([
                'status' => 'success',
                'message' => "mission restored successfully",
                //'mission' => $mission,
            ]);
        } else {
            return response()->json([
                'status' => 'warning',
                'message' => "the mission could not be restored",
            ]);
        };
    }
    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Models\Mission  $mission
     * @return \Illuminate\Http\Response
     */
    public function hardDelete($id)
    {
        //getting the deleted mission first
        $mission = Mission::withTrashed()->where('id', $id)->first();
        //delete mission files
        if (auth()->guard('api')->check()) {
            if (File::exists(public_path($mission->photo_chargement))) {
                File::delete(public_path($mission->photo_chargement));
            }
            if (File::exists(public_path($mission->photo_dechargement))) {
                File::delete(public_path($mission->photo_dechargement));
            }
            //delete from trash
            $mission->forceDelete();
            return response()->json([
                'status' => 'success',
                'message' => 'mission removed from trash successfully',
            ]);
        }
        return response()->json([
            'status' => 'danger',
            'message' => 'Unauthorized',
        ]);
    }
    // protected function guard()
    // {
    //     return Auth::guard();
    // }
}