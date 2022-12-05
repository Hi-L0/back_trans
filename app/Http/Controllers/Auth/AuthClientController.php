<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers\Auth;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;

class AuthClientController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:client', ['except' => ['login']]);
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

        if (!$token = auth()->guard('client')->attempt($request->all())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }


    protected function logout()
    {
        auth()->guard('client')->logout();
        return response()->json([
            'message' => 'user logged out successfully'
        ]);
    }
    protected function me()
    {
        return response()->json(auth()->guard('client')->user());
    }

    protected function respondWithToken($token)
    {
        $client = auth()->guard('client')->user();
        $nom = $client->nom;
        $prenom = $client->prenom;
        return response()->json([
            "message" => $nom . ' ' . $prenom . ' logged in successfully',
            'access_token' => $token,
            'client' =>  $nom . ' ' . $prenom,
            'token_type' => 'bearer',
            'isadmin' => false,
            'token_validity' => config('jwt.ttl') * 60,
            '__client' => true,

        ]);
    }
}