<?php

namespace App\Http\Controllers;

use App\Models\Agent;
use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;

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
}