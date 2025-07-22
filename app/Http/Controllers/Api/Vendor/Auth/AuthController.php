<?php

namespace App\Http\Controllers\Api\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request){
        
        $request->validate([
            'email' => 'required|email|string',
            'password' => 'required|string',
        ]);

        $vendor = Vendor::where('email', $request->email)->first();
        if (!$vendor) {
            return response()->json([
                'message' => __('This mail is incorrect'),
            ], 404);
        }
        
        if (!Hash::check($request->password, $vendor->password)) {
            return response()->json([
                'message' => __('Login fail, Password not correct'),
            ], 401);
        }
        
        if($vendor->is_active == 'pending') {
            return response()->json([
                'message' => __('waiting admin to approve your account'),
            ], 403);
        }

        $token = $vendor->createToken($request->userAgent())->plainTextToken;
        return response()->json([
            'message' => __('login successfully'),
            'token' => $token,
            'vendor' => $vendor,
        ], 200);

    }

    public function logout(Request $request){

        if (!$request->user()) {
            return response()->json([
                'message' => __('User is not authenticated.'),
            ], 401);
        }

        $token = $request->user()->currentAccessToken();

        if ($token) {
            $token->delete();
            return response()->json([
                'message' => __('Logout successfully'),
            ], 200);
        }

        return response()->json([
            'message' => __('No active access token found'),
        ], 400);
    }

}
