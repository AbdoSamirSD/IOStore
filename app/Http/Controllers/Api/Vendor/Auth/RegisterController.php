<?php

namespace App\Http\Controllers\Api\Vendor\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Vendor;
use Illuminate\Support\Facades\Hash;

class RegisterController extends Controller
{
    public function register(Request $request)
    {
            if (Vendor::where('email', $request->email)->exists()) {
            return response()->json([
                'message' => 'This email is already exists',
            ], 422);
        }
        
        $request->validate([
            'full_name' => 'required|string|max:50',
            'store_name' => 'required|string|max:25',
            'email' => 'required|email|unique:vendors,email',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'required|string|max:15',
            'address' => 'nullable|string|max:255',
            'profile_image' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'commercial_register' => 'nullable|mimes:jpeg,png,jpg,pdf|max:2048',
        ]);

        $profileImagePath = $request->hasFile('profile_image')
          ? asset('storage/' . $request->file('profile_image')->store('profile_images', 'public'))
          : null;

        $commercialRegisterPath = $request->hasFile('commercial_register')
            ? asset('storage/' . $request->file('commercial_register')->store('commercial_registers', 'public'))
            : null;

        $vendor = Vendor::create([
            'full_name' => $request->full_name,
            'store_name' => $request->store_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'profile_image' => $profileImagePath,
            'commercial_register' => $commercialRegisterPath,
            'is_active' => 'pending',
            'status' => false,
        ]);
        
        return response()->json([
            'message' => __('Rigester Successfully'),
            'vendor' => $vendor,
        ], 201);
    }
}
