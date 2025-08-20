<?php

namespace App\Http\Controllers\Api\Admin\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Admin;
use Hash;

class AdminAuthController extends Controller
{
    public function login(Request $request)
    {
        // Validate the request...
        $request->validate([
            'email' => 'required|email|exists:admins,email',
            'password' => 'required|string',
        ]);
        $admin = Admin::where('email', $request->email)->first();

        if (!$admin || !Hash::check($request->password, $admin->password)) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }

        $token = $admin->createToken('AdminToken')->plainTextToken;

        return response()->json([
            'message' => 'Login successful',
            'token' => $token,
            'admin' => [
                'id' => $admin->id,
                'name' => $admin->name,
                'email' => $admin->email,
                'phone' => $admin->phone,
            ]
        ], 200);
    }

    public function logout()
    {
        // Invalidate the token...
        $admin = auth('admin')->user();
        $admin->tokens()->delete();

        return response()->json(['message' => 'Logout successful'], 200);
    }
}
