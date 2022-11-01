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
        if (auth()->guard('api')->check()) {
            $profile = User::where('id', auth()->guard('api')->id())->get();
        } elseif (auth()->guard('agent-api')->check()) {
            $profile = Agent::where('id', auth()->guard('agent-api')->id())->get();
        } elseif (auth()->guard('client')->check()) {
            $profile = Client::where('id', auth()->guard('client')->id())->get();
        }
        return response()->json([
            'status' => 'success',
            'profile' => $profile,
        ]);
    }
}