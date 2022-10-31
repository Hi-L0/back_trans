<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthAgentController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:agent-api', ['except' => ['login']]);
    }


    public function login(Request $request)
    {
        $validate = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string|min:6'
        ]);
        if ($validate->fails()) {
            return response()->json($validate->errors(), 400);
        }

        if (!$token = auth()->guard('agent-api')->attempt($request->all())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    protected function logout()
    {
        auth()->guard('agent-api')->logout();
        return response()->json([
            'message' => 'user logged out successfully'
        ]);
    }
    protected function me()
    {
        return response()->json(auth()->guard('agent-api')->user());
    }

    protected function respondWithToken($token)
    {
        $this->agent = auth()->guard('agent-api')->user();
        $name = $this->agent->name;
        return response()->json([
            "message" => $name . ' logged in successfully',
            'access_token' => $token,
            'agent' => $this->agent->nom . ' ' . $this->agent->prenom,
            'token_type' => 'bearer',
            'isadmin' => false,
            'token_validity' => auth()->guard('agent-api')->factory()->getTTL() * 120,

        ]);
    }
}