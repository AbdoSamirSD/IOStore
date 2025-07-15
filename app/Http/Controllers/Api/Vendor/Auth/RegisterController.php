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
            'commercial_register' => 'mimes:jpeg,png,jpg,pdf|max:2048|required',
        ]);

        $vendor = Vendor::create([
            'full_name' => $request->full_name,
            'store_name' => $request->store_name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'commercial_register' => $request->file('commercial_register')->store('commercial_registers', 'public'),
            'profile_image' => $request->file('profile_image') ? $request->file('profile_image')->store('profile_images', 'public') : null,
            'is_active' => 'pending', // Default status for new vendors
            // 'commission_type' => $request->type ?? 'fixed', // Default type
            // 'commission_value' => $request->commission_value ?? 0.00, // Default commission value
            'status' => false, // Default status
        ]);

        return response()->json([
            'message' => __('Rigester Successfully'),
            'vendor' => $vendor,
        ], 201);
    }
}
