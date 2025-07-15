<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validateWithBag('login', [
            'email' => ['required', 'email', 'exists:users,email', 'string'],
            'password' => 'required',
        ]);

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'message' => trans('auth.login_fail'),
            ], 401);
        }

        $user = User::where('email', $request->email)->firstOrFail();

        $token = $user->createToken($request->userAgent())->plainTextToken;
        error_log('login');

        return response()->json([
            'message' => __('auth.login_success'),
            'token' => $token,
            'user' => $user,
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json([
            'message' => trans('auth.logout_success'),
        ], 200);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }
}
