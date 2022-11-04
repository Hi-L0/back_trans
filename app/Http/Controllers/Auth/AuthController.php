<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class AuthController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['login', 'register']]);
        // $this->middleware('guest:agent')->except(['login', 'register']);
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

        if (!$token = auth('api')->attempt($request->all())) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        return $this->respondWithToken($token);
    }

    public function register(Request $request)
    {
        $validate = Validator::make($request->all(), [
            //'name'=>'required|string',
            'nom' => 'required|string',
            'prenom' => 'required|string',
            'societe' => 'required|string',
            'email' => 'required|email',
            'password' => 'required|confirmed|min:6'
        ]);
        if ($validate->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validate->errors()
            ], 422);
        }

        $user = User::create(array_merge(
            $validate->validated(),
            [
                'password' => bcrypt($request->password),
            ]
        ));
        $admin = Role::where('code', 'admin')->get();
        $user->roles()->attach($admin[0]->id);

        return response()->json([
            'status' => 'success',
            'message' => 'user created successfully',
            'user' => $user,
        ]);
    }

    public function logout()
    {
        auth()->guard('api')->logout();
        return response()->json([
            'message' => 'user logged out successfully'
        ]);
    }
    protected function me()
    {
        return response()->json(['user' => auth()->guard('api')->user(),]);
    }


    protected function respondWithToken($token)
    {
        $this->user = auth()->guard('api')->user();
        $name = $this->user->name;
        return response()->json([
            "message" => $name . ' logged in successfully',
            'access_token' => $token,
            'user' => $this->user->nom . ' ' . $this->user->prenom,
            'isadmin' => true,
            'token_type' => 'bearer',
            'token_validity' => auth()->guard('api')->factory()->getTTL() * 60,

        ]);
    }
    // protected function guard()
    // {
    //     return Auth::guard();
    // }
}