<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\CustomAddress;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    // Register function
    public function register(Request $request)
    {
        $request->validateWithBag('register', [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:255|unique:users',
            'address' => 'array',
            'address.city' => 'required|string|max:255',
            'address.region' => 'required|string|max:255',
            'address.street' => 'required|string|max:255',
        ]);
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => new CustomAddress(
                $request->address['city'],
                $request->address['region'],
                $request->address['street']
            ),
        ]);

        $token = $user->createToken($request->userAgent())->plainTextToken;
        return response()->json([
            'message' => __('auth.register_success'),
            'token' => $token,
            'user' => $user,
        ], 201);
    }
}