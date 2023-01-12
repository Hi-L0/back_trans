<?php

namespace App\Http\Controllers\auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class ChangePasswordController extends Controller
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
    public function changePassword(Request $request)
    {
        $validatedData = Validator::make($request->all(), [
            'currentPassword' => 'required',
            'newPassword' => 'required|string|min:8|confirmed',
        ]);
        if ($validatedData->fails()) {
            return response()->json([
                'status' => 'failed',
                'errors' => $validatedData->errors()
            ], 422);
        }
        if (!(Hash::check($request->currentPassword, $this->user->password))) {
            return response()->json([
                'status' => 'warning',
                'message' => 'Your current password does not matches with your password',
            ]);
        }
        if (strcmp($request->currentPassword, $request->newPassword) == 0) {
            return response()->json([
                'status' => 'warning',
                'message' => 'New Password cannot be same as your current password',
            ]);
        }
        $this->user->password = bcrypt($request->newPassword);
        $this->user->save();
        return response()->json([
            'status' => 'success',
            'message' => 'Password successfully changed!'
        ]);
    }
}